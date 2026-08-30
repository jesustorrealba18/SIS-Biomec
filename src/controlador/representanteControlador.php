<?php

use GrupoProyecto\SisBiomec\modelo\Representante;
use GrupoProyecto\SisBiomec\modelo\Atleta;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;


if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}


$objRepresentante = new Representante();
$objAtleta = new Atleta();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');


    
    if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
        Autorizacion::exigir('representantes', 'gestionar');

       $objRepresentante->setAtributos($_POST);

        if ($objRepresentante->Eliminar()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo desactivar el registro.']);
        }
        exit;
    }



    if (isset($_POST['accion']) && $_POST['accion'] === 'reactivar') {
            Autorizacion::exigir('representantes', 'gestionar');
          
           $objRepresentante->setAtributos($_POST);

        if ($objRepresentante->Reactivar()) {
                echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo reactivar el registro.']);
        }
        exit;
    }    
    

   if (isset($_POST['accion']) && $_POST['accion'] === 'guardar') {
   
    Autorizacion::exigir('representantes', 'gestionar');
    $excluirCedula = !empty($_POST['cedula_original']) ? $_POST['cedula_original'] : null;
   
    $resultado = false; 

    $objRepresentante->setAtributos($_POST);

   
    if ($excluirCedula) {
       
        $resultado = $objRepresentante->Actualizar();
        
    } else {
        $resultado = $objRepresentante->Registrar();
      
    }

    

    if ($resultado) {
        echo json_encode(['status' => 'success', 'message' => 'Operación exitosa.']);
    } else {

         $errores = $objRepresentante->obtenerErrores(); 
            if (!empty($errores)) {
                echo json_encode(['status' => 'warning', 'errores' => $errores]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error con el servidor.']);
            }
    }
    exit; 
    }


}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {



        if (isset($_GET['accion']) && $_GET['accion'] === 'listarRepresentantes') {
            header('Content-Type: application/json');
           
            $estado = $_GET['estado'] ?? 'Activo';
            echo json_encode($objRepresentante->listarRepresentantes($estado));
            exit;
        }
    

  
    if (isset($_GET['accion']) && $_GET['accion'] === 'verPerfilAtleta' && isset($_GET['id'])) {
        header('Content-Type: application/json');
      
        $datosAtleta = $objAtleta->obtenerDetallePorId((int)$_GET['id']);
        echo json_encode($datosAtleta);
        exit;
    }
    

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarAtletas') {
        header('Content-Type: application/json');
        
        $id_rep = !empty($_GET['id_representante']) ? (int)$_GET['id_representante'] : 0;

        if ($id_rep > 0) {
           
            echo json_encode($objAtleta->listarMenoresParaRepresentante($id_rep));
        } else {
           
            echo json_encode($objAtleta->listarMenoresSinRepresentante());
        }
        exit;
    }


  
    if (isset($_GET['accion']) && $_GET['accion'] === 'obtenerRepresentante' && isset($_GET['id'])) {
        header('Content-Type: application/json');
       
        echo json_encode($objRepresentante->obtenerPorId((int)$_GET['id']));
        exit; 
    }

    require_once 'vista/representante.php';
}