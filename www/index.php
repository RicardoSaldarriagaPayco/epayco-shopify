<html>
    <head>
        <title>Welcome to LAMP Infrastructure</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    </head>
    <body>
    <form>
        <script
                src="https://checkout.epayco.co/checkout.js"
                class="epayco-button"
                data-epayco-key=""
                data-epayco-test="false"
                data-epayco-name="Audífonos REDRAGON H510 ZEUS 2"
                data-epayco-description="Audífonos REDRAGON H510 ZEUS 2"
                data-epayco-invoice="33100"
                data-epayco-currency="cop"
                data-epayco-amount="331840"
                data-epayco-tax="0"
                data-epayco-tax-base="331840"
                data-epayco-country="CO"
                data-epayco-external="false"
                data-epayco-response="https://54.166.97.49/?wc-api=WC_ePayco&amp;order_id=33100"
                data-epayco-confirmation="https://54.166.97.49/?wc-api=WC_ePayco&amp;order_id=33100&amp;confirmation=1"
                data-epayco-email-billing="ricardo.saldarriaga@payco.co"
                data-epayco-name-billing="ricardo saldarriaga"
                data-epayco-address-billing="asdasdas"
                data-epayco-lang="es"
                data-epayco-mobilephone-billing="+573198754112"
                data-epayco-button="https://54.166.97.49/wp-content/plugins/Plugin_ePayco_WooCommerce/lib/Boton-color-espanol.png"
                data-epayco-autoclick="false">
        </script>
    </form>
    <form>
        <script
            src="https://checkout.epayco.co/checkout.js"
            class="epayco-button"
            data-epayco-key=""
            data-epayco-amount="50000"
            data-epayco-name="Vestido Mujer Primavera"
            data-epayco-description="Vestido Mujer Primavera"
            data-epayco-currency="cop"
            data-epayco-country="co"
            data-epayco-test="true"
            data-epayco-external="false"
            data-epayco-response="https://ejemplo.com/respuesta.html"
            data-epayco-confirmation="https://ejemplo.com/confirmacion"
            data-epayco-methodconfirmation="get">
        </script>
    </form>
            <?php
            $signature = hash('sha256',
                trim('491027').'^'
                .trim('1fff045cec8d5bff2f8740662199dc74f8e6e612').'^'
                .'83927536'.'^'
                .'839275361648776724'.'^'
                .'38205'.'^'
                .'COP'
            );
            var_dump($signature,'63daa99df2ad37e7c80aab12bee26910fa236eb16aa6f4412d29edbe75d7422c');
            function base64url_encode($plainText) {

                $base64 = base64_encode($plainText);
                $base64url = strtr($base64, '+/=', '-_,');
                return $base64url;
            }

            function base64url_decode($plainText) {

                $base64url = strtr($plainText, '-_,', '+/=');
                $base64 = base64_decode($base64url);
                return $base64;
            }
            $data= 'secret_sJxvutu5GXat9ZDCwXYziB8hYxPEBn5V';
            $encriptar = base64url_encode($data);;
            $desemcriptar=base64url_decode($encriptar);

            //xdebug_info();
                echo "<h1>¡Hola, Richi te da la bienvenida!</h1>";

                $conn = mysqli_connect('db', 'root', 'test', "dbname");

                $query = 'SELECT * From Person';
                $result = mysqli_query($conn, $query);

                echo '<table class="table table-striped">';
                echo '<thead><tr><th></th><th>id</th><th>name</th></tr></thead>';
                while($value = $result->fetch_array(MYSQLI_ASSOC)){
                    echo '<tr>';
                    echo '<td><a href="#"><span class="glyphicon glyphicon-search"></span></a></td>';
                    foreach($value as $element){
                        echo '<td>' . $element . '</td>';
                    }

                    echo '</tr>';
                }
                echo '</table>';

                $result->close();
                mysqli_close($conn);
            ?>
        </div>
    </body>
</html>
