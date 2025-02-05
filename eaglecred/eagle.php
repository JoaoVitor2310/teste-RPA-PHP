<?php

if (isset($_POST["email"]) && isset($_POST["password"])) {
    session_start();

    // 1 - Acesar pagina inicial para gerar cookies
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://webcred.eaglecred.com.br/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_COOKIEJAR => 'cookies.txt',  // Salva os cookies recebidos
        CURLOPT_COOKIEFILE => 'cookies.txt', // Reutiliza os cookies armazenados
        CURLOPT_SSL_VERIFYPEER => false,  // Desabilitar verificação SSL
    ));

    $response = curl_exec($curl);

    // Validar
    if (!$response) {
        echo 'Erro cURL: ' . curl_error($curl);
        $_SESSION['error_message'] = 'Erro inesperado. Tente novamente.';
        header("Location: index.php");
        exit();
    }

    if (preg_match("/var\s+hashv\s*=\s*'([^']+)'/", $response, $matches)) {
        $hashv = $matches[1];
        // echo "Valor de hashv: $hashv";
    } else {
        echo 'Erro cURL: ' . curl_error($curl);
        $_SESSION['error_message'] = 'Erro inesperado. Tente novamente.';
        header("Location: index.php");
        exit();
    }


    curl_close($curl);
    // echo $hashv;
    // exit();


    // 2 - Realizar login
    // $curl = curl_init();
    // curl_setopt_array($curl, array(
    //     CURLOPT_URL => 'https://webcred.eaglecred.com.br/ajx/passwordRules.php', // https://webcred.eaglecred.com.br/ajx/login.php
    //     CURLOPT_RETURNTRANSFER => true, // 
    //     CURLOPT_ENCODING => '',
    //     CURLOPT_MAXREDIRS => 10,
    //     CURLOPT_TIMEOUT => 0,
    //     CURLOPT_FOLLOWLOCATION => true,
    //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //     CURLOPT_CUSTOMREQUEST => 'POST',
    //     CURLOPT_COOKIEJAR => 'cookies.txt',  // Salva os cookies recebidos
    //     CURLOPT_COOKIEFILE => 'cookies.txt', // Reutiliza os cookies armazenados
    //     CURLOPT_SSL_VERIFYPEER => false,  // Desabilitar verificação SSL
    //     CURLOPT_POSTFIELDS => array(
    //         'l' => $_POST["email"],
    //         // 'l' => 'CACI26',
    //         's' => $_POST["password"],
    //         // 's' => 'Futuro@123',
    //         'r' => '1',
    //         'v' => $hashv,
    //     ),
    // ));

    // $response = curl_exec($curl);
    // // exit($response);

    // // Validar
    // if (!$response) {
    //     echo 'Erro cURL: ' . curl_error($curl);
    //     $_SESSION['error_message'] = 'Erro inesperado. Tente novamente.';
    //     header("Location: index.php");
    //     exit();
    // }

    // $data = json_decode($response, true);

    // if ($data['error']) {
    //     $_SESSION['error_message'] = 'Usuário ou senha incorretos. Tente novamente.';
    //     header("Location: index.php");
    //     exit();
    // }

    // // echo $response;
    // curl_close($curl);

    // 2 - Realizar login novamente
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://webcred.eaglecred.com.br/ajx/login.php', // https://webcred.eaglecred.com.br/ajx/login.php
        CURLOPT_RETURNTRANSFER => true, // 
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_COOKIEJAR => 'cookies.txt',  // Salva os cookies recebidos
        CURLOPT_COOKIEFILE => 'cookies.txt', // Reutiliza os cookies armazenados
        CURLOPT_SSL_VERIFYPEER => false,  // Desabilitar verificação SSL
        CURLOPT_POSTFIELDS => array(
            'l' => $_POST["email"],
            's' => $_POST["password"],
            'r' => '1',
            'v' => $hashv,
        ),
    ));

    $response = curl_exec($curl);
    // exit($response); 

    // Validar
    if (!$response) {
        echo 'Erro cURL: ' . curl_error($curl);
        $_SESSION['error_message'] = 'Erro inesperado. Tente novamente.';
        header("Location: index.php");
        exit();
    }

    if ($response == 'erro|Login ou senha invalida') {
        $_SESSION['error_message'] = 'Usuário ou senha incorretos. Tente novamente.';
        header("Location: index.php");
        exit();
    }





    // Validar se enviou produtos
    if ($_POST["products"] == "") {
        $_SESSION['error_message'] = 'Nenhum produto foi enviado. Tente novamente.';
        header("Location: index.php");
        exit();
    }

    // Separar o id dos produtos em um array
    $produtos = str_replace("NOME - ", "", $_POST["products"]);
    $produtos = explode(',', $produtos);

    // 2 - Permitir todos os usuários para o produto
    foreach ($produtos as $produto) {
        $produto = trim($produto);
        $produto = str_pad($produto, 6, "0", STR_PAD_LEFT);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://webcred.eaglecred.com.br/ajx/ajx_parametrizaproduto.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYPEER => false,  // Desabilitar verificação SSL
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_COOKIEJAR => 'cookies.txt',  // Salva os cookies recebidos
            CURLOPT_COOKIEFILE => 'cookies.txt', // Reutiliza os cookies armazenados
            CURLOPT_POSTFIELDS => '{
            "CodProduto": "' . $produto . '",
            "usuarios": [],
            "todosForamSelecionados": true,
            "process": "permitiremlote"
        }',
        ));

        $response = curl_exec($curl);
        // var_dump($response);
        // die;

        if (!$response) {
            $_SESSION['error_message'] = 'Erro ao autorizar usuários no produto: ' . $produto . '. Tente novamente.';
            header("Location: index.php");
            exit();
        }

        // var_dump($response);
        curl_close($curl);
    }
    $_SESSION['success_message'] = 'Usuários de produtos permitidos com sucesso.';
    header("Location: index.php");
    exit();
}