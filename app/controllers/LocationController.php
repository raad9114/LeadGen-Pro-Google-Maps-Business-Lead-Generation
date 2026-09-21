<?php
/**
 * Location Controller (Countries, Cities, Areas)
 */

class LocationController
{
    public function index(): void
    {
        Session::requireAuth();
        $pageTitle = 'Locations';
        $currentPage = 'locations';

        $locationModel = new Location();
        $countries = $locationModel->getCountries(false);
        $allCities = $locationModel->getAllCities();
        $allAreas = $locationModel->getAllAreas();

        include TEMPLATE_PATH . '/layouts/app.php';
    }

    public function countries(): void
    {
        Session::requireAuth();
        $locationModel = new Location();
        Response::success($locationModel->getCountries());
    }

    public function cities(string $countryId): void
    {
        Session::requireAuth();
        $locationModel = new Location();
        Response::success($locationModel->getCities((int) $countryId));
    }

    public function areas(string $cityId): void
    {
        Session::requireAuth();
        $locationModel = new Location();
        Response::success($locationModel->getAreas((int) $cityId));
    }

    public function storeCountry(): void
    {
        Session::requireAdmin();
        CSRF::validateOrDie();

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $name = trim($data['name'] ?? '');
        $code = trim($data['code'] ?? '');

        if (empty($name) || empty($code)) {
            Response::error('Country name and code are required');
            return;
        }

        $locationModel = new Location();
        $id = $locationModel->createCountry($name, $code);
        Response::success(['id' => $id], 'Country added');
    }

    public function storeCity(): void
    {
        Session::requireAdmin();
        CSRF::validateOrDie();

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $countryId = (int) ($data['country_id'] ?? 0);
        $name = trim($data['name'] ?? '');

        if (empty($name) || $countryId <= 0) {
            Response::error('City name and country are required');
            return;
        }

        $locationModel = new Location();
        $id = $locationModel->createCity($countryId, $name,
            $data['latitude'] ?? null, $data['longitude'] ?? null);
        Response::success(['id' => $id], 'City added');
    }

    public function storeArea(): void
    {
        Session::requireAdmin();
        CSRF::validateOrDie();

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $cityId = (int) ($data['city_id'] ?? 0);
        $name = trim($data['name'] ?? '');

        if (empty($name) || $cityId <= 0) {
            Response::error('Area name and city are required');
            return;
        }

        $locationModel = new Location();
        $id = $locationModel->createArea($cityId, $name,
            $data['latitude'] ?? null, $data['longitude'] ?? null);
        Response::success(['id' => $id], 'Area added');
    }

    public function deleteCountry(string $id): void
    {
        Session::requireAdmin();
        CSRF::validateOrDie();
        $locationModel = new Location();
        $locationModel->deleteCountry((int) $id);
        Response::success(null, 'Country deleted');
    }

    public function deleteCity(string $id): void
    {
        Session::requireAdmin();
        CSRF::validateOrDie();
        $locationModel = new Location();
        $locationModel->deleteCity((int) $id);
        Response::success(null, 'City deleted');
    }

    public function deleteArea(string $id): void
    {
        Session::requireAdmin();
        CSRF::validateOrDie();
        $locationModel = new Location();
        $locationModel->deleteArea((int) $id);
        Response::success(null, 'Area deleted');
    }
}
