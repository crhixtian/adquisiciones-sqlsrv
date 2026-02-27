<?php

class Controller {
    /**
     * Render a view file located under app/views.
     * Variables passed in $data will be extracted for use in the view.
     */
    protected function render($view, $data = []) {
        extract($data);
        require __DIR__ . "/../views/layouts/header.php";
        require __DIR__ . "/../views/{$view}.php";
        require __DIR__ . "/../views/layouts/footer.php";
    }

    /**
     * Simple redirect helper.
     */
    protected function redirect($url) {
        header("Location: $url");
        exit;
    }
}
