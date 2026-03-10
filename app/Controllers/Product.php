<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\ProductModel;

class Product extends BaseController
{
    private const UPLOAD_DIR = 'uploads/products';
    private const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private const MAX_SIZE   = 2048; // KB

    public function index()
    {
        helper('url');
        $model = new ProductModel();
        $db   = \Config\Database::connect();
        $rows = $db->table('products p')
            ->select('p.*, c.name as category_name')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->orderBy('p.name')
            ->get()
            ->getResultArray();
        foreach ($rows as &$r) {
            $r['stock_status'] = ProductModel::getStockStatus((int) ($r['stock'] ?? 0), (int) ($r['min_stock'] ?? 0));
        }
        return view('templates/template', [
            'title'     => 'Products | KTV Bistro POS',
            'bodyClass' => 'layout-dashboard',
            'content'   => view('products/index', ['products' => $rows]),
        ]);
    }

    public function create()
    {
        helper(['form', 'url']);
        $catModel = new CategoryModel();
        return view('templates/template', [
            'title'     => 'Add Product | KTV Bistro POS',
            'bodyClass' => 'layout-dashboard',
            'content'   => view('products/form', [
                'product' => null,
                'categories' => $catModel->orderBy('name')->findAll(),
                'action'   => site_url('products/store'),
            ]),
        ]);
    }

    public function store()
    {
        $rules = [
            'name'        => 'required|max_length[150]',
            'category_id' => 'required|integer',
            'price'       => 'required|decimal',
            'stock'       => 'permit_empty|integer|greater_than_equal_to[0]',
            'min_stock'   => 'permit_empty|integer|greater_than_equal_to[0]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $imagePath = $this->handleImageUpload('image');
        $model = new ProductModel();
        $model->insert([
            'name'        => trim($this->request->getPost('name')),
            'category_id' => (int) $this->request->getPost('category_id'),
            'price'       => (float) $this->request->getPost('price'),
            'stock'       => (int) ($this->request->getPost('stock') ?: 0),
            'min_stock'   => (int) ($this->request->getPost('min_stock') ?: 0),
            'image'       => $imagePath,
        ]);
        return redirect()->to(site_url('products'))->with('success', 'Product created.');
    }

    public function edit(int $id)
    {
        helper(['form', 'url']);
        $model = new ProductModel();
        $product = $model->find($id);
        if (! $product) {
            return redirect()->to(site_url('products'))->with('error', 'Product not found.');
        }
        $catModel = new CategoryModel();
        return view('templates/template', [
            'title'     => 'Edit Product | KTV Bistro POS',
            'bodyClass' => 'layout-dashboard',
            'content'   => view('products/form', [
                'product'    => $product,
                'categories' => $catModel->orderBy('name')->findAll(),
                'action'     => site_url('products/update/' . $id),
            ]),
        ]);
    }

    public function update(int $id)
    {
        $model = new ProductModel();
        $product = $model->find($id);
        if (! $product) {
            return redirect()->to(site_url('products'))->with('error', 'Product not found.');
        }
        $rules = [
            'name'        => 'required|max_length[150]',
            'category_id' => 'required|integer',
            'price'       => 'required|decimal',
            'stock'       => 'permit_empty|integer|greater_than_equal_to[0]',
            'min_stock'   => 'permit_empty|integer|greater_than_equal_to[0]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $imagePath = $this->handleImageUpload('image');
        $data = [
            'name'        => trim($this->request->getPost('name')),
            'category_id' => (int) $this->request->getPost('category_id'),
            'price'       => (float) $this->request->getPost('price'),
            'stock'       => (int) ($this->request->getPost('stock') ?: 0),
            'min_stock'   => (int) ($this->request->getPost('min_stock') ?: 0),
        ];
        if ($imagePath !== null) {
            $data['image'] = $imagePath;
        }
        $model->update($id, $data);
        return redirect()->to(site_url('products'))->with('success', 'Product updated.');
    }

    public function delete(int $id)
    {
        $model = new ProductModel();
        $product = $model->find($id);
        if (! $product) {
            return redirect()->to(site_url('products'))->with('error', 'Product not found.');
        }
        $db   = \Config\Database::connect();
        $used = $db->table('order_items')->where('product_id', $id)->countAllResults();
        if ($used > 0) {
            return redirect()->to(site_url('products'))->with('error', 'Cannot delete: product is used in ' . $used . ' order(s).');
        }
        $model->delete($id);
        return redirect()->to(site_url('products'))->with('success', 'Product deleted.');
    }

    private function handleImageUpload(string $field): ?string
    {
        $file = $this->request->getFile($field);
        if (! $file || ! $file->isValid() || $file->getError() !== UPLOAD_ERR_OK) {
            return null;
        }
        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, self::ALLOWED_EXT, true)) {
            return null;
        }
        if ($file->getSize() > self::MAX_SIZE * 1024) {
            return null;
        }
        $dir = FCPATH . self::UPLOAD_DIR;
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $newName = $file->getRandomName();
        $file->move($dir, $newName);
        return self::UPLOAD_DIR . '/' . $newName;
    }
}
