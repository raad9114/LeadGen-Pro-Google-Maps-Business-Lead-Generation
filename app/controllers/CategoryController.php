<?php
/**
 * Category Controller
 */

class CategoryController
{
    public function index(): void
    {
        Session::requireAuth();
        $pageTitle = 'Categories';
        $currentPage = 'categories';

        $categoryModel = new Category();
        $categories = $categoryModel->getAll(false);

        include TEMPLATE_PATH . '/layouts/app.php';
    }

    public function list(): void
    {
        Session::requireAuth();
        $categoryModel = new Category();
        Response::success($categoryModel->getAll());
    }

    public function store(): void
    {
        Session::requireAdmin();
        CSRF::validateOrDie();

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $name = trim($data['name'] ?? '');

        if (empty($name)) {
            Response::error('Category name is required');
            return;
        }

        $categoryModel = new Category();
        $id = $categoryModel->create([
            'name' => $name,
            'icon' => $data['icon'] ?? 'bi-building',
            'variants' => isset($data['variants']) ? json_encode($data['variants']) : null,
            'is_active' => $data['is_active'] ?? 1,
        ]);

        Response::success(['id' => $id], 'Category created');
    }

    public function update(string $id): void
    {
        Session::requireAdmin();
        CSRF::validateOrDie();

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $categoryModel = new Category();
        if (!empty($data['variants']) && is_array($data['variants'])) {
            $data['variants'] = json_encode($data['variants']);
        }
        $categoryModel->update((int) $id, $data);

        Response::success(null, 'Category updated');
    }

    public function delete(string $id): void
    {
        Session::requireAdmin();
        CSRF::validateOrDie();

        $categoryModel = new Category();
        $categoryModel->delete((int) $id);

        Response::success(null, 'Category deleted');
    }
}
