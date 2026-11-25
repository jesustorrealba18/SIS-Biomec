<?php
/* namespace GrupoProyecto\SisBiomec\modelo;

use PDO;

if (!class_exists(Conexion::class, false)) {
    class Conexion {
        protected ?PDO $conex1 = null;
        
        public function __construct(string $db = '') {
            // Usamos SQLite en memoria
            $this->conex1 = new PDO('sqlite::memory:');
            $this->conex1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conex1->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Magia para emular funciones de MySQL en SQLite
            
            // 1. NOW() -> Para fechas de modificación
            $this->conex1->sqliteCreateFunction('NOW', function () {
                return date('Y-m-d H:i:s');
            }, 0);

            // 2. CURDATE() -> Para fecha actual
            $this->conex1->sqliteCreateFunction('CURDATE', function () {
                return date('Y-m-d');
            }, 0);

            // 3. CONCAT() -> Para unir nombres y apellidos
            $this->conex1->sqliteCreateFunction('CONCAT', function (...$args) {
                return implode('', $args);
            }, -1);
            
            // 4. TIMESTAMPDIFF(YEAR, fecha, CURDATE()) -> Para calcular edades
            $this->conex1->sqliteCreateFunction('TIMESTAMPDIFF', function ($unit, $start, $end) {
                if (strtoupper($unit) === 'YEAR') {
                    $date1 = new \DateTime($start);
                    $date2 = new \DateTime($end);
                    return $date2->format('Y') - $date1->format('Y');
                }
                return 0;
            }, 3);

            // 5. FIELD() -> Para ordenar los días de la semana en listarHorariosActivos
            $this->conex1->sqliteCreateFunction('FIELD', function (...$args) {
                $valor = array_shift($args);
                $indice = array_search($valor, $args);
                return $indice !== false ? $indice + 1 : 0;
            }, -1);
        }
        
        public function getConex1(): PDO {
            return $this->conex1;
        }
    }
} */


namespace GrupoProyecto\SisBiomec\modelo;

use PDO;

if (!class_exists(Conexion::class, false)) {
    class Conexion {
        protected ?PDO $pdo = null;
        protected ?PDO $conex1 = null;

        public function __construct(string $db = '') {
            $this->conex1 = new PDO('sqlite::memory:');
            $this->conex1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conex1->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Sincroniza $this->pdo para modelos que heredan la propiedad directamente
            $this->pdo = $this->conex1;

            // Emulación de funciones MySQL en SQLite
            $this->conex1->sqliteCreateFunction('NOW', fn() => date('Y-m-d H:i:s'), 0);
            $this->conex1->sqliteCreateFunction('CURDATE', fn() => date('Y-m-d'), 0);
            $this->conex1->sqliteCreateFunction('CONCAT', fn(...$args) => implode('', $args), -1);
            $this->conex1->sqliteCreateFunction('TIMESTAMPDIFF', function ($unit, $start, $end) {
                if (strtoupper($unit) === 'YEAR') {
                    return (new \DateTime($end))->format('Y') - (new \DateTime($start))->format('Y');
                }
                return 0;
            }, 3);
            $this->conex1->sqliteCreateFunction('FIELD', function (...$args) {
                $valor = array_shift($args);
                $indice = array_search($valor, $args);
                return $indice !== false ? $indice + 1 : 0;
            }, -1);
        }

        public function getConex1(): PDO {
            return $this->conex1;
        }
    }
}