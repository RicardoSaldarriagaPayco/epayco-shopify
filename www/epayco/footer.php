


</main>
<script src="https://unpkg.com/@shopify/app-bridge@3">

</script><script src="https://unpkg.com/@shopify/app-bridge-utils"></script>
<script>
    var AppBridge = window['app-bridge'];
    var AppBridgeUtil = window['app-bridge-utils'];
    var actions = window['app-bridge'].actions;

    var TitleBar = actions.TitleBar;
    var Button = actions.Button;
    var Redirect = actions.Redirect;
    var Modal = actions.Modal;

    var app = AppBridge.createApp({
        apiKey:'6ce386c42826963b2ca0cb879d0fd258',
        host: new URLSearchParams(location.search).get("host"),
    });

    const modalOpt = {
        title: 'Example Title',
        message: 'I am the content inside of the modal'
    }

    const exampleModal = Modal.create(app, modalOpt);

    const redirect = Redirect.create(app);
     var installScriptBtn = Button.create(app, { label: 'Activar ePayco' });

        installScriptBtn.subscribe(Button.Action.CLICK, data =>{
            var p_cust_id = document.getElementsByName("p_cust_id")[0].value.replace(/ /g, "");
            if(p_cust_id.length <= 0){
               document.getElementById("p_cust_id").className +=' error'
            }else{
                document.getElementById("p_cust_id").className = 'row'
            }
            var public_key = document.getElementsByName("public_key")[0].value.replace(/ /g, "");
            if(public_key.length <= 0){
                document.getElementById("public_key").className +=' error'
            }else{
                document.getElementById("public_key").className = 'row'
            }
            var p_key = document.getElementsByName("p_key")[0].value.replace(/ /g, "");
            if(p_key.length <= 0){
                document.getElementById("p_key").className +=' error'
            }else{
                document.getElementById("p_key").className = 'row'
            }
            var checkout_test = document.getElementById("checkout_test").checked ? 'true' : 'false';

            if(p_cust_id.length > 0 && public_key.length > 0 && p_key.length > 0){
                document.getElementsByName("p_cust_id")[0].value = p_cust_id;
                document.getElementsByName("public_key")[0].value = public_key;
                document.getElementsByName("p_key")[0].value = p_key;
                const input = document.createElement("input");
                input.setAttribute("type", "text");
                input.setAttribute('name',"test_type");
                input.value = checkout_test;
                document.getElementById("epayco_checkout").appendChild(input);

                document.getElementById("epayco_checkout").submit();
            }

        });



    const titleBarOpt = {
        title: 'ePayco',
        buttons: {
            primary: installScriptBtn
        }
    }
    const appTitleBar = TitleBar.create(app, titleBarOpt);

    //==========================
    //GETTING SESSION TOKEN
    //==========================

    const getSessionToken = AppBridgeUtil.getSessionToken;
    getSessionToken(app).then(token => {
        var formData = new FormData();
        formData.append('token', token)
       fetch('verify_session.php', {
           method: 'POST',
           header: {
               'Content-Type': 'application/json'
           },
           body: formData
       }).then(response => response.json())
           .then(data => console.log(data))

    });

</script>
</body>
</html>
