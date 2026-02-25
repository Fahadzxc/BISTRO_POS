<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KtvRoomModel;
use App\Models\KtvSessionModel;
use App\Models\ProductModel;

class KtvRooms extends BaseController
{
    private const POS_CART_KEY = 'pos_cart';

    public function index()
    {
        helper('url');

        return view('templates/template', [
            'title'     => 'KTV Rooms | KTV Bistro POS',
            'bodyClass' => 'layout-dashboard layout-ktv',
            'content'   => view('ktv_rooms/ktv_rooms', [
                'urlGetRooms'    => site_url('ktv-rooms/get-rooms'),
                'urlStart'       => site_url('ktv-rooms/start'),
                'urlPause'       => site_url('ktv-rooms/pause'),
                'urlResume'      => site_url('ktv-rooms/resume'),
                'urlEnd'         => site_url('ktv-rooms/end'),
                'urlSetAvailable'=> site_url('ktv-rooms/set-available'),
                'csrfToken'      => csrf_hash(),
                'csrfName'       => csrf_token(),
            ]),
        ]);
    }

    /**
     * AJAX: Get all rooms with current session and timer info (for polling).
     */
    public function getRooms()
    {
        $roomModel   = new KtvRoomModel();
        $sessionModel = new KtvSessionModel();
        $rooms       = $roomModel->orderBy('room_name')->findAll();
        $result      = [];

        foreach ($rooms as $room) {
            $session = $sessionModel->getActiveByRoom((int) $room['id']);
            $elapsed = 0;
            $currentBill = 0;
            $status = $room['status'];
            $sessionId = null;
            $sessionStatus = null;

            if ($session) {
                $sessionId     = (int) $session['id'];
                $sessionStatus = $session['status'];
                $elapsed       = $sessionModel->getElapsedSeconds($session);
                $hours         = $elapsed / 3600;
                $currentBill   = round($hours * (float) $room['hourly_rate'], 2);
            }

            $result[] = [
                'id'            => (int) $room['id'],
                'room_name'     => $room['room_name'],
                'hourly_rate'   => (float) $room['hourly_rate'],
                'status'        => $status,
                'session_id'    => $sessionId,
                'session_status'=> $sessionStatus,
                'elapsed'       => $elapsed,
                'current_bill'  => $currentBill,
            ];
        }

        return $this->response->setJSON($result);
    }

    /**
     * Start a new session.
     */
    public function start()
    {
        $roomId = (int) $this->request->getPost('room_id');
        $roomModel = new KtvRoomModel();
        $sessionModel = new KtvSessionModel();

        $room = $roomModel->find($roomId);
        if (! $room) {
            return $this->response->setJSON(['success' => false, 'message' => 'Room not found']);
        }
        if ($room['status'] !== 'available') {
            return $this->response->setJSON(['success' => false, 'message' => 'Room is not available']);
        }
        if ($sessionModel->getActiveByRoom($roomId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Room already has an active session']);
        }

        $sessionModel->insert([
            'room_id'   => $roomId,
            'start_time'=> date('Y-m-d H:i:s'),
            'cashier_id'=> session()->get('user_id'),
            'status'    => 'active',
        ]);

        $roomModel->update($roomId, ['status' => 'occupied']);

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Pause session.
     */
    public function pause()
    {
        $roomId = (int) $this->request->getPost('room_id');
        $sessionModel = new KtvSessionModel();
        $session = $sessionModel->getActiveByRoom($roomId);

        if (! $session) {
            return $this->response->setJSON(['success' => false, 'message' => 'No active session']);
        }
        if ($session['status'] === 'paused') {
            return $this->response->setJSON(['success' => false, 'message' => 'Session already paused']);
        }

        $pausedAt = time();
        $prevPaused = (int) ($session['total_paused_seconds'] ?? 0);
        $sessionModel->update($session['id'], [
            'status'               => 'paused',
            'paused_at'            => date('Y-m-d H:i:s', $pausedAt),
            'total_paused_seconds' => $prevPaused,
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Resume session.
     */
    public function resume()
    {
        $roomId = (int) $this->request->getPost('room_id');
        $sessionModel = new KtvSessionModel();
        $session = $sessionModel->getActiveByRoom($roomId);

        if (! $session || $session['status'] !== 'paused') {
            return $this->response->setJSON(['success' => false, 'message' => 'No paused session']);
        }

        $pausedAt = strtotime($session['paused_at']);
        $extraPaused = time() - $pausedAt;
        $prevPaused = (int) ($session['total_paused_seconds'] ?? 0);

        $sessionModel->update($session['id'], [
            'status'               => 'active',
            'paused_at'            => null,
            'total_paused_seconds' => $prevPaused + $extraPaused,
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * End session: compute bill, update room status, add room charge to POS cart.
     */
    public function end()
    {
        $roomId = (int) $this->request->getPost('room_id');
        $roomModel = new KtvRoomModel();
        $sessionModel = new KtvSessionModel();

        $room = $roomModel->find($roomId);
        if (! $room) {
            return $this->response->setJSON(['success' => false, 'message' => 'Room not found']);
        }

        $session = $sessionModel->getActiveByRoom($roomId);
        if (! $session) {
            return $this->response->setJSON(['success' => false, 'message' => 'No active session']);
        }

        $elapsedSeconds = $sessionModel->getElapsedSeconds($session);
        $totalMinutes   = (int) ceil($elapsedSeconds / 60);
        $totalAmount    = KtvSessionModel::computeAmount((float) $room['hourly_rate'], $totalMinutes);
        $hoursLabel     = $totalMinutes < 60
            ? $totalMinutes . ' min'
            : round($totalMinutes / 60, 1) . ' hr' . ($totalMinutes % 60 ? 's' : '');

        $sessionModel->update($session['id'], [
            'end_time'          => date('Y-m-d H:i:s'),
            'total_minutes'     => $totalMinutes,
            'total_amount'      => $totalAmount,
            'status'            => 'ended',
        ]);

        $roomModel->update($roomId, ['status' => 'cleaning']);

        $this->addRoomChargeToCart($room['room_name'], $totalAmount, $hoursLabel);

        return $this->response->setJSON([
            'success'       => true,
            'total_minutes' => $totalMinutes,
            'total_amount'  => $totalAmount,
            'hours_label'   => $hoursLabel,
        ]);
    }

    /**
     * Add room charge to current user's POS cart.
     */
    private function addRoomChargeToCart(string $roomName, float $amount, string $hoursLabel): void
    {
        $productModel = new ProductModel();
        $roomChargeProduct = $productModel->where('name', 'KTV Room Charge')->first();
        if (! $roomChargeProduct) {
            return;
        }

        $key = 'room_' . uniqid();
        $cart = session()->get(self::POS_CART_KEY) ?? [];
        $cart[$key] = [
            'product_id' => (int) $roomChargeProduct['id'],
            'name'       => $roomName . ' (' . $hoursLabel . ')',
            'price'      => $amount,
            'qty'        => 1,
            'subtotal'   => $amount,
        ];
        session()->set(self::POS_CART_KEY, $cart);
    }

    /**
     * Set room status to available (e.g. after cleaning).
     */
    public function setAvailable()
    {
        $roomId = (int) $this->request->getPost('room_id');
        $roomModel = new KtvRoomModel();
        $room = $roomModel->find($roomId);
        if (! $room) {
            return $this->response->setJSON(['success' => false, 'message' => 'Room not found']);
        }
        $roomModel->update($roomId, ['status' => 'available']);
        return $this->response->setJSON(['success' => true]);
    }
}
