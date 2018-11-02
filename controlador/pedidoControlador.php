<?php
require "modelo/produtoModelo.php";
require_once "modelo/usuarioModelo.php";
require_once "modelo/pedidoModelo.php";

/* user,admin*/
function index(){
    if(ehPost()){
        extract($_POST);
        $desconto = pegarDecontoCupom($cupom);
        
        if(!empty($desconto)){
            $total_atual = $_SESSION["carrinho"]["total"];
            $total = $total_atual - ($total_atual * ($desconto/100));
            $_SESSION["carrinho"]["total"] = $total;
            alert("Desconto aplicado com sucesso!<br>","success");
        }else{
            alert("Cupom não encontrado!<br>","danger"); 
        }
    }
    
    $carrinhoProdutos = $_SESSION["carrinho"];
    $dados["produtos"] = pegarVariosProdutosPorId($carrinhoProdutos);
    
    //pegar os dados do cliente logado
    $id_cliente = $_SESSION['auth']['codCliente'];
    $dados["cliente"] = pegarUsuarioPorId($id_cliente);
    
    exibir("pedido/index",$dados);
}

/* user,admin*/
function finalizar($codCliente){
	$carrinhoProdutos = $_SESSION["carrinho"];
    $dados["produtos"] = pegarVariosProdutosPorId($carrinhoProdutos);

    $data_pedido = strftime("%Y/%m/%d") . " " . strftime("%H:%M:%S");

    $id_cliente = $_SESSION['auth']['codCliente'];
    $dadosCliente = pegarUsuarioPorId($id_cliente);

    $id_pedido = inserirPedido($id_cliente, $dadosCliente['CPF'], $dadosCliente['Pais'],$dadosCliente['Estado'],$dadosCliente['Municipio'],$dadosCliente['endereco'],$data_pedido,$_SESSION["carrinho"]["total"]);

    foreach ($dados["produtos"] as $produto) {
        inserirProdutosPedidoId($id_pedido,$produto["CodProduto"],$produto["quantidade"]);
        updateEstoqueProduto($produto["CodProduto"], $produto["quantidade"], $produto["Estoque"]);
    }

    unset($_SESSION["carrinho"]);
    redirecionar("produto/index");
}

function listar(){
    $id_cliente = $_SESSION['auth']['codCliente'];

    if($pedidos = pegarPedidos($id_cliente)){
        $dados["pedidos"] = $pedidos;
    }else{
        $dados["pedidos"] = "";
    }

    exibir("pedido/listar", $dados);
}