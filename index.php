<?php 
require_once 'Wow.php'; // Incluye la clase Wow.php que contiene las definiciones de las clases Jugador y Boss
session_start(); // Inicia la sesión para mantener el estado del juego entre solicitudes

if (isset($_SESSION['intentos'])) { // Si ya hay un intento registrado en la sesión, se recuperan los valores de intentos y jefes derrotados
    Raid::$intentosTotales = $_SESSION['intentos'];
    Raid::$bossesDerrotados = $_SESSION['derrotados'];
}

else {  // Si no hay intentos registrados en la sesión, se inicializan los contadores de intentos y jefes derrotados a 0
    $_SESSION['intentos'] = 0;
    $_SESSION['derrotados'] = 0;
}

if (!isset($_SESSION['j1']) || isset($_POST['reiniciar'])) {    // Si no hay un jugador en la sesión o se ha solicitado reiniciar, se crean nuevas instancias de Jugador y Boss
    
       Raid::NuevoIntento(); 
    $_SESSION['intentos'] = Raid::$intentosTotales; // Guardamos el avance    

    $_SESSION['j1'] = new Jugador("Linuss");    // Crea un nuevo jugador llamado "Linuss"
    $_SESSION['b1'] = new Boss("Illidan");  // Crea un nuevo jefe llamado "Illidan"
    $_SESSION['victoria_registrada'] = false; // Bandera para no contar la victoria dos veces
    
    $_SESSION['log'] = "¡Comienza la pelea (Intento #" . Raid::$intentosTotales . ")! Linuss se enfrenta a Illidan.";
}

if (!isset($_SESSION['j1']) || isset($_POST['reiniciar'])) { // Si no hay un jugador en la sesión o se ha solicitado reiniciar, se crean nuevas instancias de Jugador y Boss
    $_SESSION['j1'] = new Jugador("Linuss"); // Crea un nuevo jugador llamado "Linuss"
    $_SESSION['b1'] = new Boss("Illidan");  // Crea un nuevo jefe llamado "Illidan"
    $_SESSION['log'] = "¡Comienza la pelea! Linuss se enfrenta a Illidan."; // Inicializa el registro de combate
}

$j1 = $_SESSION['j1']; // Recupera el jugador de la sesión
$b1 = $_SESSION['b1']; // Recupera el jefe de la sesión

if (isset($_POST['accion']) && $b1->VidaBoss > 0 && $j1->VidaJugador > 0) { // Si se ha enviado una acción y ambos, el jugador y el jefe, están vivos, se procesa la acción
    $opcion = $_POST['accion']; // Obtiene la acción seleccionada por el jugador
    ob_start();     // Inicia la captura de salida para registrar los eventos del combate

    // Turno del Jugador
    if ($opcion == "1") {   // Si el jugador selecciona "Ataque Básico", se llama al método AtaqueBasico del jugador, pasando el jefe como argumento
        $j1->AtaqueBasico($b1); 
    } elseif ($opcion == "2") {     // Si el jugador selecciona "Golpe Heroico", se llama al método GolpeHeroico del jugador, pasando el jefe como argumento
        $j1->GolpeHeroico($b1); 
    } elseif ($opcion == "3") { // Si el jugador selecciona "Poción de Vida", se llama al método UsarPocion del jugador
        $j1->UsarPocion();
    } elseif ($opcion == "4") { // Si el jugador selecciona "Poción de Maná", se llama al método UsarPocionMana del jugador
        $j1->UsarPocionMana();
    }

    // Turno del Boss
    if ($b1->VidaBoss > 0) {    // Si el jefe sigue vivo después del turno del jugador, se procede con su turno
        echo "\n--- TURNO DEL BOSS ---\n";      // Se imprime un mensaje indicando que es el turno del jefe
        
        if ($b1->VidaBoss <= 250 && $b1->Rage == false) {   // Si la vida del jefe es menor o igual a 250 y no está en modo Rage, se activa el modo Rage
            $b1->Fase2();
        }

        $danioDelBoss = $b1->Rage ? 15 : 7; // Si el jefe está en modo Rage, su daño es 15; de lo contrario, es 7
        echo "{$b1->Nombre} ataca a {$j1->Nombre} haciéndole {$danioDelBoss} de daño.\n";   // Se imprime el daño que el jefe inflige al jugador
        $j1->JugadorGolpeado($danioDelBoss);    // Se llama al método JugadorGolpeado del jugador, pasando el daño del jefe como argumento para reducir la vida del jugador

        if ($j1->VidaJugador <= 0) {    // Si la vida del jugador es menor o igual a 0 después del ataque del jefe, se imprime un mensaje indicando que el jugador ha recibido un golpe mortal y ha sido derrotado
            echo "\n{$j1->Nombre} recibió un golpe mortal... WIPE.\n";  // Mensaje de derrota del jugador
        }
    } else {    // Si la vida del jefe es menor o igual a 0 después del turno del jugador, se imprime un mensaje indicando que el jefe ha muerto y el jugador ha ganado
        echo "\n¡VICTORIA! {$b1->Nombre} murió. ¡Felicidades!\n";
        echo "Intentos totales: " . Raid::$intentosTotales . " | Jefes derrotados: " . Raid::$bossesDerrotados . "\n"; // Se imprime el número total de intentos y jefes derrotados
        if (!$_SESSION['victoria_registrada']) {   // Si la victoria no ha sido registrada aún, se llama al método BossDerrotado de la clase Raid para incrementar el contador de jefes derrotados y se marca la victoria como registrada
            Raid::BossDerrotado();
            $_SESSION['derrotados'] = Raid::$bossesDerrotados; // Guardamos el avance
            $_SESSION['victoria_registrada'] = true; // Marcamos que la victoria ha sido registrada
        }
    }

    $_SESSION['log'] .= "\n-----------------------------\n" . ob_get_clean();   // Se guarda el registro del combate en la sesión, agregando una línea separadora y el contenido capturado durante el turno
}

$registroBatalla = $_SESSION['log'];    // Se asigna el registro del combate a la variable $registroBatalla para mostrarlo en la interfaz de usuario
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Simulador de Raid</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #1e1e1e; color: #fff; text-align: center; padding: 20px; }
        .panel { background-color: #333; padding: 20px; border-radius: 10px; display: inline-block; width: 600px; margin-bottom: 20px;}
        .stats { font-size: 18px; margin: 10px 0; font-weight: bold;}
        .raid-stats { color: #f1c40f; font-size: 16px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px;}
        .hp-boss { color: #ff4c4c; }
        .hp-jugador { color: #4cff4c; }
        .mana { color: #4c4cff; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; font-size: 16px; border: none; border-radius: 5px;}
        .btn-ataque { background-color: #ddd; }
        .btn-especial { background-color: #ff9900; color: white;}
        .btn-pocion { background-color: #00cc66; color: white;}
        .btn-mana { background-color: #3399ff; color: white;}
        .btn-reinicio { background-color: #ff3333; color: white;}
        pre { background-color: #000; padding: 15px; border-radius: 5px; text-align: left; font-size: 16px; height: 300px; overflow-y: auto;}
        h3 { margin-bottom: 5px; color: #aaa; }
    </style>
</head>
<body>

    <h1>Batalla contra el Jefe</h1>

    <div class="panel">
        <!-- Estadísticas de la clase Raid implementadas en la vista -->
        <div class="stats raid-stats">⭐ Bosses Derrotados: <?= Raid::$bossesDerrotados ?> | Intento Actual: #<?= Raid::$intentosTotales ?> ⭐</div>
        
        <div class="stats hp-boss">ENEMIGO: <?= $b1->Nombre ?> - Vida: <?= $b1->VidaBoss ?></div>
        <hr>
        <div class="stats hp-jugador">JUGADOR: <?= $j1->Nombre ?> - Vida: <?= $j1->VidaJugador ?></div>
        <div class="stats mana">Maná: <?= $j1->Mana ?> | Pociones Vida: <?= $j1->Pociones ?> | Pociones Maná: <?= $j1->PocionesMana ?></div>
    </div>

    <h3>Registro de combate</h3>
    <div class="panel">
        <pre><?= $registroBatalla ?></pre>
    </div>

    <form method="POST">
        <?php if ($b1->VidaBoss > 0 && $j1->VidaJugador > 0): ?>
            <button type="submit" name="accion" value="1" class="btn-ataque">Ataque Básico</button>
            <button type="submit" name="accion" value="2" class="btn-especial">Golpe Heroico (20 Maná)</button>
            <button type="submit" name="accion" value="3" class="btn-pocion">Poción de Vida</button>
            <button type="submit" name="accion" value="4" class="btn-mana">Poción de Maná</button>
        <?php else: ?>
            <button type="submit" name="reiniciar" value="1" class="btn-reinicio">Reiniciar Pelea (Nuevo Intento)</button>
        <?php endif; ?>
    </form>

    <script>
        let consola = document.querySelector('pre');
        consola.scrollTop = consola.scrollHeight;
    </script>
</body>
</html>