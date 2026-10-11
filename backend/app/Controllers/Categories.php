<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductModel;

class Categories extends BaseController
{
    public function index()
    {
        $model = new CategoryModel();

        if ($this->request->is('post')) {
            $this->handlePost($model);

            return redirect()->to('categories.php');
        }

        $search = trim((string) $this->request->getGet('search'));

        return $this->page('categories', [
            'pageTitle'                => 'Category Management - EGOTECH WORLD',
            'search'                   => $search,
            'categories'               => $model->withProductCount($search),
            'totalCategories'          => $model->countAllResults(),
            'totalCategorizedProducts' => (new ProductModel())
                ->where('category_id IS NOT NULL', null, false)->where('status', 'Active')->countAllResults(),
        ]);
    }

    private function handlePost(CategoryModel $model): void
    {
        $action = (string) $this->request->getPost('action');
        $name   = trim((string) $this->request->getPost('name'));
        $desc   = trim((string) $this->request->getPost('description'));
        $id     = (int) $this->request->getPost('id');

        if ($action === 'add_category') {
            if ($model->insert(['name' => $name, 'description' => $desc])) {
                $this->flash('Category created successfully!');
            } else {
                $this->flash('Failed to create category: ' . implode(' ', $model->errors()), 'error');
            }
        } elseif ($action === 'edit_category') {
            if ($id <= 0 || $name === '') {
                $this->flash('Please specify a valid category name.', 'error');
            } elseif ($model->update($id, ['id' => $id, 'name' => $name, 'description' => $desc])) {
                $this->flash('Category updated successfully!');
            } else {
                $this->flash('Failed to update category: ' . implode(' ', $model->errors()), 'error');
            }
        } elseif ($action === 'delete_category' && $id > 0) {
            try {
                $model->delete($id);   // products keep existing: category_id becomes NULL (FK ON DELETE SET NULL)
                $this->flash('Category deleted successfully.');
            } catch (\Throwable $e) {
                log_message('error', 'Category delete failed: {m}', ['m' => $e->getMessage()]);
                $this->flash('Delete failed: this category is in use.', 'error');
            }
        }
    }
}
