<?= view('layouts/_sidebar', ['currentPage' => 'ktv_rooms']) ?>

<div class="main-wrapper">
    <header class="top-navbar d-flex justify-content-between align-items-center">
        <span class="nav-title">KTV Rooms</span>
        <div class="user-info">
            <span class="text-muted small"><?= esc(session()->get('name')) ?></span>
            <span class="badge bg-warning text-dark role-badge"><?= esc(session()->get('role')) ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-left me-1"></i>Logout
            </a>
        </div>
    </header>

    <main class="content-area">
        <div class="ktv-grid" id="ktvGrid">
            <div class="text-center py-5 text-muted">
                <div class="spinner-border"></div>
                <p class="mt-2 mb-0">Loading rooms...</p>
            </div>
        </div>
    </main>
</div>

<!-- End Session Confirm Modal -->
<div class="modal fade" id="endSessionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">End Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>End session for <strong id="endRoomName"></strong>?</p>
                <p class="mb-0">Current bill: <strong id="endRoomBill"></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="endSessionConfirmBtn">
                    <i class="bi bi-stop-fill me-1"></i>End Session
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const KTV = {
    cfg: {
        getRooms: '<?= $urlGetRooms ?>',
        start: '<?= $urlStart ?>',
        pause: '<?= $urlPause ?>',
        resume: '<?= $urlResume ?? $urlStart ?>',
        end: '<?= $urlEnd ?>',
        setAvailable: '<?= $urlSetAvailable ?? $urlStart ?>',
        csrfName: '<?= $csrfName ?>',
        csrfToken: '<?= $csrfToken ?>'
    },
    pollInterval: 4000,
    timerInterval: null,
    endRoomId: null,
    formatTime(seconds) {
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        return [h, m, s].map(v => String(v).padStart(2, '0')).join(':');
    },
    formatPrice(n) {
        return '₱' + parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    },
    async fetch(url, data = {}) {
        const body = new FormData();
        body.append(this.cfg.csrfName, this.cfg.csrfToken);
        for (const k in data) body.append(k, data[k]);
        const r = await fetch(url, { method: 'POST', body });
        const text = await r.text();
        try { return JSON.parse(text); } catch (e) { return { success: false }; }
    },
    async getRooms() {
        const r = await fetch(this.cfg.getRooms);
        const text = await r.text();
        try { return JSON.parse(text); } catch (e) { return []; }
    },
    render(rooms) {
        const grid = document.getElementById('ktvGrid');
        if (!grid) return;
        grid.innerHTML = rooms.map(room => {
            const hasSession = room.session_id && (room.session_status === 'active' || room.session_status === 'paused');
            const statusClass = 'status-' + room.status;
            const statusLabel = room.status.charAt(0).toUpperCase() + room.status.slice(1);
            const timerStr = hasSession ? this.formatTime(room.elapsed) : '--:--:--';
            const billStr = hasSession ? this.formatPrice(room.current_bill) : '₱0.00';
            let actions = '';
            if (room.status === 'available') {
                actions = `<button type="button" class="btn btn-success btn-start" data-room-id="${room.id}"><i class="bi bi-play-fill me-1"></i>Start</button>`;
            } else if (room.status === 'occupied' && hasSession) {
                if (room.session_status === 'active') {
                    actions = `<button type="button" class="btn btn-warning btn-pause" data-room-id="${room.id}"><i class="bi bi-pause-fill me-1"></i>Pause</button>`;
                } else {
                    actions = `<button type="button" class="btn btn-info btn-resume" data-room-id="${room.id}"><i class="bi bi-play-fill me-1"></i>Resume</button>`;
                }
                actions += ` <button type="button" class="btn btn-danger btn-end" data-room-id="${room.id}" data-room-name="${room.room_name}" data-bill="${room.current_bill}"><i class="bi bi-stop-fill me-1"></i>End</button>`;
            } else if (room.status === 'cleaning') {
                actions = `<button type="button" class="btn btn-outline-success btn-available" data-room-id="${room.id}"><i class="bi bi-check-lg me-1"></i>Available</button>`;
            }
            return `
            <div class="ktv-room-card ${statusClass}" data-room-id="${room.id}">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="room-name">${room.room_name}</span>
                    <span class="badge bg-${room.status === 'available' ? 'success' : room.status === 'occupied' ? 'danger' : 'warning'}">${statusLabel}</span>
                </div>
                <div class="room-timer">${timerStr}</div>
                <div class="room-bill">${billStr}</div>
                <div class="small text-muted">₱${parseFloat(room.hourly_rate).toFixed(0)}/hr</div>
                <div class="room-actions mt-2">${actions}</div>
            </div>
            `;
        }).join('');

        grid.querySelectorAll('.btn-start').forEach(btn => btn.addEventListener('click', () => this.start(btn.dataset.roomId)));
        grid.querySelectorAll('.btn-pause').forEach(btn => btn.addEventListener('click', () => this.pause(btn.dataset.roomId)));
        grid.querySelectorAll('.btn-resume').forEach(btn => btn.addEventListener('click', () => this.resume(btn.dataset.roomId)));
        grid.querySelectorAll('.btn-end').forEach(btn => btn.addEventListener('click', () => this.showEndModal(btn.dataset.roomId, btn.dataset.roomName, btn.dataset.bill)));
        grid.querySelectorAll('.btn-available').forEach(btn => btn.addEventListener('click', () => this.setAvailable(btn.dataset.roomId)));
    },
    async start(roomId) {
        const r = await this.fetch(this.cfg.start, { room_id: roomId });
        if (r.success) this.poll(); else alert(r.message || 'Failed');
    },
    async pause(roomId) {
        const r = await this.fetch(this.cfg.pause, { room_id: roomId });
        if (r.success) this.poll(); else alert(r.message || 'Failed');
    },
    async resume(roomId) {
        const r = await this.fetch(this.cfg.resume, { room_id: roomId });
        if (r.success) this.poll(); else alert(r.message || 'Failed');
    },
    showEndModal(roomId, roomName, bill) {
        this.endRoomId = roomId;
        document.getElementById('endRoomName').textContent = roomName;
        document.getElementById('endRoomBill').textContent = this.formatPrice(bill);
        new bootstrap.Modal(document.getElementById('endSessionModal')).show();
    },
    async endSession() {
        if (!this.endRoomId) return;
        const r = await this.fetch(this.cfg.end, { room_id: this.endRoomId });
        bootstrap.Modal.getInstance(document.getElementById('endSessionModal')).hide();
        this.endRoomId = null;
        if (r.success) {
            alert('Session ended. Bill: ' + this.formatPrice(r.total_amount) + '. Room charge added to POS cart.');
            this.poll();
        } else {
            alert(r.message || 'Failed');
        }
    },
    async setAvailable(roomId) {
        const r = await this.fetch(this.cfg.setAvailable, { room_id: roomId });
        if (r.success) this.poll(); else alert(r.message || 'Failed');
    },
    async poll() {
        const rooms = await this.getRooms();
        if (Array.isArray(rooms)) this.render(rooms);
    }
};

document.getElementById('endSessionConfirmBtn').addEventListener('click', () => KTV.endSession());

document.addEventListener('DOMContentLoaded', () => {
    KTV.poll();
    setInterval(() => KTV.poll(), KTV.pollInterval);
});
</script>
