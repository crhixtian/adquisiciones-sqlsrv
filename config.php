define('DEV_MODE', true); // true = estás en tu laptop
$menu_items = [];

if(defined('DEV_MODE') && DEV_MODE === true){

    // MENÚ SIMULADO LOCAL
    $_SESSION['usuario_id'] = 1;
    $_SESSION['usuario_nombre'] = 'Cristian Dev';
    $_SESSION['usuario_rol'] = 'ADMIN';

    $menu_items = [
        ["nombre" => "dashboard", "etiqueta" => "Dashboard", "icono" => "home"],
        ["nombre" => "adquisiciones", "etiqueta" => "Adquisiciones TI", "icono" => "device-laptop"],
        ["nombre" => "usuarios", "etiqueta" => "Usuarios", "icono" => "users"],
    ];

} else {

    // CONEXIÓN REAL INSTITUCIONAL
    // use Database class from MVC structure
    require_once __DIR__ . '/app/core/Database.php';
    $conn = Database::connect();

    if (isset($_SESSION['usuario_id'])) {
        $sql_menu = "SELECT m.nombre, m.etiqueta, m.icono 
                     FROM comun.Modulos m
                     INNER JOIN comun.Permisos p ON m.id_modulo = p.id_modulo
                     WHERE p.id_usuario = ? AND p.pueden_ver = 1
                     ORDER BY m.orden ASC";

        $stmt_menu = $conn->prepare($sql_menu);
        $stmt_menu->execute([$_SESSION['usuario_id']]);
        $menu_items = $stmt_menu->fetchAll();
    }
}