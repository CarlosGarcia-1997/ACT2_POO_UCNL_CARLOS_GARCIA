<?php
require_once 'Index.php';   // Incluye la clase Index.php que contiene las definiciones de las clases Jugador y Boss
class Jugador { // Clase que representa al jugador en el juego
    public string $Nombre;  
    public int $VidaJugador = 100;  
    public int $Mana = 100;    
    public int $Pociones = 3;
    public int $PocionesMana = 3;      
    public static int $JugadoresVivos = 0;
    public function __construct(string $nombre) {

        $this->Nombre = $nombre;    // Inicializa el nombre del jugador y establece la vida, maná y pociones iniciales para el jugador que se crea
        self::$JugadoresVivos++;    // Incrementa el contador de jugadores vivos cada vez que se crea un nuevo jugador
    }

    public function JugadorGolpeado(int $danioJugador) {    // Método que se llama cuando el jugador recibe daño
        $this->VidaJugador -= $danioJugador;        //Protección: la vida nunca baja de 0 
        $this->VidaJugador = max($this->VidaJugador, 0); // Evitamos que la vida sea negativa
    }
    
    public function AtaqueBasico(Boss $objetivo) {  // Método que representa el ataque básico del jugador, que inflige daño al jefe
        echo "[{$this->Nombre}] ataca a {$objetivo->Nombre} con su espada.\n";  
        $objetivo->RecibirDanio(60);    
    }

  
    public function GolpeHeroico(Boss $objetivo) {  // Método que representa el golpe heroico del jugador, que inflige un daño mayor al jefe pero consume maná
        if ($this->Mana >= 20) {
            echo "[{$this->Nombre}] usa GOLPE HEROICO! Un ataque devastador.\n";
            $this->Mana -= 20; 
            $objetivo->RecibirDanio(90);
        } 
        
        else {    // Si el jugador no tiene suficiente maná, se imprime un mensaje indicando que no puede usar el golpe heroico y pierde el turno
            echo "[{$this->Nombre}] intenta usar Golpe Heroico pero NO TIENE MANÁ. Pierde el turno.\n";
        }
    }

    public function UsarPocion() {  // Método que representa el uso de una poción de vida por parte del jugador, que recupera vida si tiene pociones disponibles
        if ($this->Pociones > 0) {  
            $this->VidaJugador += 100;  // Recupera 100 de vida    
            $this->VidaJugador = min($this->VidaJugador, 100);  // Evitamos que la vida exceda el máximo permitido
            $this->Pociones--;  // Disminuye el número de pociones disponibles
            echo "[{$this->Nombre}] se bebe una poción y recupera vida. (Vida actual: {$this->VidaJugador})\n";
        } 
        
        else {
            echo "[{$this->Nombre}] busca en sus bolsillos pero YA NO TIENE POCIONES DE VIDA. Pierde el turno.\n";  // Si no tiene pociones, se imprime un mensaje indicando que no puede usar una poción y pierde el turno
        }
    }
    
    public function UsarPocionMana() { 
        if ($this->PocionesMana > 0) {
            $this->Mana += 30; 
            $this->PocionesMana--;
            echo "[{$this->Nombre}] se bebe una poción azul y recupera maná. (Maná actual: {$this->Mana})\n";
        } 
        
        else {
            echo "[{$this->Nombre}] busca en sus bolsillos pero YA NO TIENE POCIONES DE MANÁ. Pierde el turno.\n";
        }
    }
}

class Boss {    //Clase que representa al jefe en el juego
private static float $VidaBossOriginal = 500;
public string $Nombre;
public float $VidaBoss;
public bool $Rage = false;

    public function __construct(string $nombre) {   // Inicializa el nombre del jefe y establece su vida inicial, que es de 500 puntos
        $this->Nombre = $nombre;
        $this->VidaBoss = self::$VidaBossOriginal;
    }

    public function RecibirDanio(float $danio) {    // Método que se llama cuando el jefe recibe daño
        if ($this->Rage) {
            $this->VidaBoss -= $danio / 2;
        }
        
        else {  // Si el jefe no está en modo Rage, recibe el daño completo
            $this->VidaBoss -= $danio;
        }
        $this->VidaBoss = max($this->VidaBoss, 0);
    }

    public function Fase2() {   // Método que activa el modo Rage del jefe cuando su vida es menor o igual a 250
        echo "{$this->Nombre} entra en rage\n";
        $this->Rage = true;
        $this->VidaBoss = self::$VidaBossOriginal;
        echo "{$this->Nombre} se cura a tope\n";
    }
}

class Raid {    // Clase que representa la raid en el juego, que lleva un registro de los intentos totales y los jefes derrotados
public static int $intentosTotales = 0;
public static int $bossesDerrotados = 0;

    public static function NuevoIntento() { // Método que se llama cada vez que se inicia un nuevo intento de raid, incrementando el contador de intentos totales
        self::$intentosTotales++;   // Incrementa el contador de intentos totales cada vez que se inicia un nuevo intento de raid
    }

    public static function BossDerrotado() { // Método que se llama cuando un jefe es derrotado, incrementando el contador de jefes derrotados
        self::$bossesDerrotados++;
    }
}
?>