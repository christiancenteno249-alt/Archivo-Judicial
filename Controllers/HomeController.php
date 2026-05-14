<?php
/**
 * Controllers/HomeController.php
 * Controlador del menú principal (dashboard).
 */
class HomeController extends Controller {

    public function index(): void {
        $this->requireAuth();
        $this->render('home/index');
    }
}
