<?php
// clase base para controladores con helpers de renderizado y redirección

class Controller {

    protected function render($view, $data = []) {
        extract($data);
        require __DIR__ . "/../views/layouts/header.php";
        require __DIR__ . "/../views/{$view}.php";
        require __DIR__ . "/../views/layouts/footer.php";
    }

    protected function redirect($url) {
        header("Location: $url");
        exit;
    }
}
