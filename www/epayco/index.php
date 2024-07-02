<?php
include_once("includes/mysql_connect.php");
include_once("includes/shopify.php");

/**
 * create the variables:
 * - $shopify
 */
$shopify = new Shopify();

/**
 * checking the shopify store
 */
$url_shop = $_GET['shop'];
$shopify->set_url($url_shop);
$is_install=true;
include_once("includes/check_token.php");
//include_once("includes/check.php");
$webhook = [
    "webhook" => [
        "topic" => "app/uninstalled",
        "address" => "https://shop.epayco.io/uninstall.php",
        "format" => "json"
    ]
];
$shopify->rest_api('/admin/api/2024-01/webhooks.json', $webhook, 'POST');
/**
 * here display anything about the store
 */

$resultEpayco = $mysql->query("SELECT * FROM epayco_shopify_credentials WHERE shop_url= '". $_GET['shop'] ."' LIMIT 1");
$redirectUrl=$shopify->returnPaymentAdmin();
if($credentialsResult->num_rows == 0){
    $store_data = "no hay datos";
    $p_cust_id = '';
    $public_key = '';
    $private_key = '';
    $p_key = '';
    $tipe_checkout = 'one_page';
    $language_checkout = 'es';
    $checkout_test = 'true';
}else {
    $store_data = $credentialsData;
    $p_cust_id = $store_data["p_cust_id"];
    $public_key = $store_data["public_key"];
    $private_key = $store_data["private_key"];
    $p_key = $store_data["p_key"];
    $tipe_checkout = $store_data["tipe_checkout"];
    $language_checkout = $store_data["language_checkout"];
    $checkout_test = $store_data['checkout_test'];
}
$alert= '<div></div>';

if($_SERVER['REQUEST_METHOD'] == 'POST' ){
    if($_POST['action_type'] == 'load_credentials'){
        if($resultEpayco->num_rows < 1){
            $querySettings = "INSERT INTO epayco_shopify_credentials (
                    p_cust_id, 
                    public_key, 
                    private_key, 
                    p_key, 
                    tipe_checkout, 
                    checkout_test, 
                    language_checkout,
                    shop_url
        ) VALUES (
         '".$_POST['p_cust_id']."',
         '".$_POST['public_key']."',
         '".$_POST['private_key']."',
         '".$_POST['p_key']."',
         '".$_POST['tipe_checkout']."',
         '".$_POST['test_type']."',
          '".$_POST['language_checkout']."',
          '".$_GET['shop']."'
           ) ON DUPLICATE KEY UPDATE public_key='".$_POST['public_key']."' ";

            if($mysql->query($querySettings)){
                $shopify->redirectPaymentOption('true');
                $querySettings = "UPDATE shopify_shop SET is_active = 2 WHERE shop_url ='".$url_shop."' ";
                if($mysql->query($querySettings)){
                    echo sprintf('
                        <script>
                        (function() {
                            setTimeout(function(){
                                top.location="%s"
                            },2000);
                        })();
                        </script>
                    ',$redirectUrl);
                }
            }else{
                echo '<div class="alert error">
                  <dl>
                    <dt>Error</dt>
                    <dd>ocurrio un error!</dd>
                  </dl>
                </div>
                ';
            }
        }else{
            $querySettings = "UPDATE epayco_shopify_credentials  SET p_cust_id=
         '".$_POST['p_cust_id']."',
         public_key=
         '".$_POST['public_key']."',
         private_key=
         '".$_POST['private_key']."',
         p_key=
         '".$_POST['p_key']."',
         tipe_checkout=
         '".$_POST['tipe_checkout']."',
         checkout_test=
         '".$_POST['test_type']."',
         language_checkout=
          '".$_POST['language_checkout']."'
            WHERE shop_url ='".$_GET['shop']."' ";

            if($mysql->query($querySettings)){
                if(is_null($store_data["is_active"])){
                    $shopify->redirectPaymentOption('true');
                    $querySettings = "UPDATE shopify_shop SET is_active = 2 WHERE shop_url ='".$url_shop."' ";
                    if($mysql->query($querySettings)){
                        echo sprintf('
                            <script>
                            (function() {
                                setTimeout(function(){
                                    top.location="%s"
                                },2000);
                            })();
                            </script>
                        ',$redirectUrl);
                    }
                }
            }else{
                echo '<div class="alert error">
                  <dl>
                    <dt>Error</dt>
                    <dd>ocurrio un error!</dd>
                  </dl>
                </div>';
            }
        }
        $p_cust_id = $_POST['p_cust_id'];
        $public_key = $_POST['public_key'];
        $private_key = $_POST['private_key'];
        $p_key = $_POST['p_key'];
        $language_checkout = $_POST['language_checkout'];
    }

    if($_POST['action_active'] == 'active_payment'){
        $shopify->redirectPaymentOption();
    }
}

?>


<?php include_once("header.php"); ?>

    <article>
        <form action="" method="post" name="epayco_checkout" id="epayco_checkout" onsubmit="ePaycoSettingUp(event)">
            <input type="hidden" name="action_type" value="load_credentials">
            <section>
                <div class="card">
                    <h5>ePayco</h5>
                    <p>
                        Instructivo de implementación:
                    </p>
                    <p>
                        1. Crear una cuenta  <a href="https://epayco.com/" target="_blank">ePayco</a>
                    </p>
                    <p>
                        2. Iniciar sesión en tu cuenta <a href="https://dashboard.epayco.com/login" target="_blank">ePayco</a>
                    </p>
                    <p>
                        3. Haz clic en el módulo "Integraciones" y en el submódulo de "Llaves API" se
                        encontrarán las llaves secretas (credenciales de seguridad proporcionadas por ePayco)
                    </p>
                    <p>
                        Obtén ayuda de <a href="https://epayco.com/contacto" target="_blank">ePayco</a>
                    </p>
                    <hr>
                </div>
                <hr/>
            </section>
            <section>
                <div class="card">
                    <h5>Información de la cuenta</h5>
                    <div  id="p_cust_id" class="row">
                        <label><h6>id de comercio</h6>
                        </label>
                        <input type="text" name="p_cust_id" value="<?php echo $p_cust_id; ?>"/>
                    </div>
                    <div id="public_key" class="row">
                        <label><h6>public key</h6>
                        </label>
                        <input type="text" name="public_key" value="<?php echo $public_key; ?>"/>
                    </div>
                    <div id="private_key" class="row">
                        <label><h6>private key</h6>
                        </label>
                        <input type="text" name="private_key" value="<?php echo $private_key; ?>"/>
                    </div>
                    <div id="p_key" class="row">
                        <label><h6>p key</h6>
                        </label>
                        <input type="text" name="p_key" value="<?php echo $p_key; ?>" />
                    </div>
                    <!--<div class="row">
                <label><h6>tipo de checkout</h6>
                </label>
                <select  name="tipe_checkout" id="tipe_checkout">
                    <?php if($tipe_checkout == 'onpage'){ ?>
                    <option value="onpage" selected>Onpage</option>
                    <option value="standar">Standar</option>
                    <?php }else{?>
                     <option value="onpage">Onpage</option>
                     <option value="standar" selected>Standar</option>
                    <?php }?>
                </select>
            </div>
            <div class="row">
                <br>
                <label>
                    <input type="checkbox" name="checkout_test" <?php echo $checkout_test=='true'? 'checked="checked"': '' ?>  id="checkout_test" >Activar modo de prueba</label>
            </div>-->
                </div>
            </section>
            <section>
                <div class="card">
                    <h5>Idioma</h5>
                    <div class="row">
                        <label><h6>Selecciona idioma</h6>
                        </label>
                        <select id="language_checkout" name="language_checkout">
                            <?php if($language_checkout == 'es'){ ?>
                                <option value="es" selected>Español</option>
                                <option value="en">Inglés</option>
                            <?php }else{?>
                                <option value="es">Español</option>
                                <option value="en" selected>Inglés</option>
                            <?php }?>
                        </select>
                    </div>
                </div>
            </section>
            <button type="submit">Guardar datos</button>
        </form>
        <br>
        <form action="" method="post" name="active_checkout" id="active_checkout">
        <input type="hidden" name="action_active" value="active_payment">
        </form>
    </article>
<?php include_once("footer.php"); ?>
