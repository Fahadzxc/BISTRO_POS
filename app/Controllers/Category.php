<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CategoryModel;

class Category extends BaseController
{
    public function index()
    {
        helper('url');
        $model = new CategoryModel();
        return view('templates/template', [
            'title'     => 'Categories | KTV Bistro POS',
            'bodyClass' => 'layout-dashboard',
            'content'   => view('categories/index', [
                'categories' => $model->orderBy('name')->findAll(),
            ]),
        ]);
    }

    public function create()
    {
        helper(['form', 'url']);
        return view('templates/template', [
            'title'     => 'Add Category | KTV Bistro POS',
            'bodyClass' => 'layout-dashboard',
            'content'   => view('categories/form', [
                'category' => null,
                'action'   => site_url('categories/store'),
            ]),
        ]);
    }

    public function store()
    {
        $rules = [
            'name' => [
                'rules'  => 'required|max_length[100]',
                'errors' => ['required' => 'Category name is required.'],
            ],
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $model = new CategoryModel();
        $model->insert(['name' => trim($this->request->getPost('name'))]);
        return redirect()->to(site_url('categories'))->with('success', 'Category created.');
    }

    public function edit(int $id)
    {
        helper(['form', 'url']);
        $model = new CategoryModel();
        $category = $model->find($id);
        if (! $category) {
            return redirect()->to(site_url('categories'))->with('error', 'Category not found.');
        }
        return view('templates/template', [
            'title'     => 'Edit Category | KTV Bistro POS',
            'bodyClass' => 'layout-dashboard',
            'content'   => view('categories/form', [
                'category' => $category,
                'action'   => site_url('categories/update/' . $id),
            ]),
        ]);
    }

    public function update(int $id)
    {
        $model = new CategoryModel();
        if (! $model->find($id)) {
            return redirect()->to(site_url('categories'))->with('error', 'Category not found.');
        }
        $rules = [
            'name' => [
                'rules'  => 'required|max_length[100]',
                'errors' => ['required' => 'Category name is required.'],
            ],
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $model->update($id, ['name' => trim($this->request->getPost('name'))]);
        return redirect()->to(site_url('categories'))->with('success', 'Category updated.');
    }

    public function delete(int $id)
    {
        $model = new CategoryModel();
        $category = $model->find($id);
        if (! $category) {
            return redirect()->to(site_url('categories'))->with('error', 'Category not found.');
        }
        $db   = \Config\Database::connect();
        $used = $db->table('products')->where('category_id', $id)->countAllResults();
        if ($used > 0) {
            return redirect()->to(site_url('categories'))->with('error', 'Cannot delete: category is in use by ' . $used . ' product(s).');
        }
        $model->delete($id);
        return redirect()->to(site_url('categories'))->with('success', 'Category deleted.');
    }
}
