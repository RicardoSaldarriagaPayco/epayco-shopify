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

include_once("includes/check_token.php");

/**
 * here display anything about the store
 */
 $access_scopes = $shopify->rest_api('/admin/oauth/access_scopes.json', array(), 'GET');
 $response = json_decode($access_scopes['body'], true);

$queryPayment = "SELECT * FROM epayco WHERE shop_url= '". $_GET['shop'] ."' LIMIT 1";
$result = $mysql->query($queryPayment);

if($result->num_rows > 1){
    $store_data = "no hay datos";
    $p_cust_id = '';
    $public_key = '';
    $p_key = '';
    $tipe_checkout = 'one_page';
    $language_checkout = 'es';
    $checkout_test = 'true';
}else {
    $store_data = $result->fetch_assoc();
    $p_cust_id = $store_data["p_cust_id"];
    $public_key = $store_data["public_key"];
    $p_key = $store_data["p_key"];
    $tipe_checkout = $store_data["tipe_checkout"];
    $language_checkout = $store_data["language_checkout"];
    $checkout_test = $store_data['checkout_test'];
}
$alert= '<div></div>';
if($_SERVER['REQUEST_METHOD'] == 'POST' ){
    if($result->num_rows < 1){
        $querySettings = "INSERT INTO epayco (
                    p_cust_id, 
                    public_key, 
                    p_key, 
                    tipe_checkout, 
                    checkout_test, 
                    language_checkout,
                    shop_url
        ) VALUES (
         '".$_POST['p_cust_id']."',
         '".$_POST['public_key']."',
         '".$_POST['p_key']."',
         '".$_POST['tipe_checkout']."',
         '".$_POST['test_type']."',
          '".$_POST['language_checkout']."',
          '".$_GET['shop']."'
           ) ON DUPLICATE KEY UPDATE public_key='".$_POST['public_key']."' ";

        if(!$mysql->query($querySettings)){
            $alert= '<div class="alert error">
                      <dl>
                        <dt>Error Alert</dt>
                        <dd>Ocurrio un error!</dd>
                      </dl>
                    </div>';
        }else{
            $alert= '<div class="alert success">
              <dl>
                <dt>Success</dt>
                <dd>Datos guardados!</dd>
              </dl>
            </div>';
        }
    }else{
        $querySettings = "UPDATE epayco  SET p_cust_id=
         '".$_POST['p_cust_id']."',
         public_key=
         '".$_POST['public_key']."',
         p_key=
         '".$_POST['p_key']."',
         tipe_checkout=
         '".$_POST['tipe_checkout']."',
         checkout_test=
         '".$_POST['test_type']."',
         language_checkout=
          '".$_POST['language_checkout']."'
            WHERE shop_url ='".$_GET['shop']."' ";

        if(!$mysql->query($querySettings)){
            $alert= '<div class="alert error">
                      <dl>
                        <dt>Error Alert</dt>
                        <dd>Ocurrio un error!</dd>
                      </dl>
                    </div>';
        }else{
            $alert= '<div class="alert success">
              <dl>
                <dt>Success</dt>
                <dd>Datos guardados!</dd>
              </dl>
            </div>';
        }
    }


}
?>


<?php include_once("header.php"); ?>
<?php
    $query = array("query" => "{
        shop {
            id
            name
            email
        }
    }");
    $graphql_test = $shopify->graph_ql($query);
    $graphql_test = json_decode($graphql_test['body']);
    //echo $alert;
?>
<script>
    setTimeout(function(){
        console.log('cargando')
        
    }, 2000);
</script>
<article>
    <form action="" method="post" name="epayco_checkout" id="epayco_checkout">
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
            Obtén ayuda de <a href="https://epayco.com/" target="_blank">ePayco</a>
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
            <div id="p_key" class="row">
                <label><h6>p key</h6>
                </label>
                <input type="text" name="p_key" value="<?php echo $p_key; ?>" />
            </div>
            <div class="row">
                <label><h6>tipo de checkout</h6>
                </label>
                <select  name="tipe_checkout" id="tipe_checkout">
                    <?php if($tipe_checkout == 'one_page'){ ?>
                    <option value="one_page" selected>One page</option>
                    <option value="standartd">Standart</option>
                    <?php }else{?>
                     <option value="one_page">One page</option>
                     <option value="standartd" selected>Standart</option>
                    <?php }?>
                </select>
            </div>
            <div class="row">
                <br>
                <label>
                    <input type="checkbox" name="checkout_test" <?php echo $checkout_test=='true'? 'checked="checked"': '' ?>  id="checkout_test" >Activar modo de prueba</label>
            </div>
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
                        <option value="es" selected>español</option>
                        <option value="en">English</option>
                    <?php }else{?>
                        <option value="es">español</option>
                        <option value="en" selected>English</option>
                    <?php }?>
                </select>
            </div>
        </div>
    </section>
    </form>
</article>
<?php include_once("footer.php"); ?>

