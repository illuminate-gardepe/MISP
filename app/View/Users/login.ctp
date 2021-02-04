<div style="width:100%;">
    <?php
        echo $this->Session->flash('auth');
    ?>
<table style="margin-left:auto;margin-right:auto;">
    <tr>
    <td style="text-align:right;width:250px;padding-right:50px">
        <?php if (Configure::read('MISP.welcome_logo')) echo $this->Html->image('custom/' . h(Configure::read('MISP.welcome_logo')), array('alt' => __('Logo'), 'onerror' => "this.style.display='none';")); ?>
    </td>
    <td style="width:460px">
        <span style="font-size:18px;">
            <?php
                if (Configure::read('MISP.welcome_text_top')) {
                    echo h(Configure::read('MISP.welcome_text_top'));
                }
            ?>
        </span><br /><br />
        <div>
        <?php if (Configure::read('MISP.main_logo') && file_exists(APP . '/webroot/img/custom/' . Configure::read('MISP.main_logo'))): ?>
            <img src="<?php echo $baseurl?>/img/custom/<?php echo h(Configure::read('MISP.main_logo'));?>" style=" display:block; margin-left: auto; margin-right: auto;" />
        <?php else: ?>
            <img src="<?php echo $baseurl?>/img/misp-logo.png" style="display:block; margin-left: auto; margin-right: auto;"/>
        <?php endif;?>
        </div>
        <?php
            if (true == Configure::read('MISP.welcome_text_bottom')):
        ?>
                <div style="text-align:right;font-size:18px;">
                <?php
                    echo h(Configure::read('MISP.welcome_text_bottom'));
                ?>
                </div>
        <?php
            endif;
            echo $this->Form->create('User');
        ?>
        <?php
            function console_log($output, $with_script_tags = true) {
                $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . 
            ');';
                if ($with_script_tags) {
                    $js_code = '<script>' . $js_code . '</script>';
                }
                echo $js_code;
            }

            // TODO: This is Apache code 
           // $client_cn = explode(" ",  $_SERVER['SSL_CLIENT_S_DN_CN']);
           // $certid = end($client_cn);

           // $cert = openssl_x509_parse('/etc/certs/mysql/client-cert.pem');
           // $email = $cert['email'];

            // console.log("EMAIL IS: " . $email);

            // TODO: Mitch - this is a copy and paste. Might need to change params
            $pdo = new PDO('mysql:dbname=misp;host=db', 'misp', 'misp', array(
                // TODO: Mitch - what is this .pem file exactly?
                // We will have to make sure any auth files are in a volume-mapped directory 
                // so MISP can access them
                PDO::MYSQL_ATTR_SSL_CA => '/etc/certs/mysql/client-cert.pem',
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
                )
            );

            foreach(getallheaders() as $name => $value) {
                if($name == "SSL-EMAIL") {
                    $email = $value;
                }
                console_log($name . ": " . $value);
            }

           // $pdo -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           // $stmt = $pdo->prepare("SELECT email FROM users WHERE certid='$certid' LIMIT 1");
           // $stmt -> execute();
           // $dbemail = $stmt -> fetch();

            function generateRandomString($length = 20) {
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < $length; $i++){
                    $randomString .= $characters[rand(0, $charactersLength -1)];
                }
                return $randomString;
            }

            // $randompass = generateRandomString();
            // $change_password = shell_exec("/var/www/MISP/app/Console/cake Password -q $email $randompass 2>&1");
            // $savecertid = $pdo->prepare("UPDATE users SET certid='$certid' where email='$email'");
           //  $savecertid -> execute();
            $changepw = $pdo->prepare("UPDATE users SET change_pw='0' where email='$email'");
            $changepw -> execute();

            $pdo = null;

            echo $this->Form->input('email', array('autocomplete' => 'off', 'value' => $email));
            echo $this->Form->input('password', array('autocomplete' => 'off', 'value' => "Password1234!!!!"));
        ?>
            <div class="clear">
            <?php
                echo empty(Configure::read('Security.allow_self_registration')) ? '' : sprintf(
                    '<a href="%s/users/register" title="%s">%s</a>',
                    $baseurl,
                    __('Registration will be sent to the administrators of the instance for consideration.'),
                    __('No account yet? Register now!')
                );
            ?>
            </div>
            <div style="text-align:center">
                <?php
                echo $this->Form->button('<h4>        Login with PKI        </h4>', array('class' => 'btn btn-primary'));
                echo $this->Form->end();
                ?>
            </div>
    </td>
    <td style="width:250px;padding-left:50px">
        <?php if (Configure::read('MISP.welcome_logo2')) echo $this->Html->image('custom/' . h(Configure::read('MISP.welcome_logo2')), array('alt' => 'Logo2', 'onerror' => "this.style.display='none';")); ?>
    </td>
    </tr>
    </table>
</div>

<script>
$(function() {
    $('#UserLoginForm').submit(function(event) {
        event.preventDefault()
        submitLoginForm()
    });
})

function submitLoginForm() {
    var $form = $('#UserLoginForm')
    var url = $form.attr('action')
    var email = $form.find('#UserEmail').val()
    var password = $form.find('#UserPassword').val()
    if (!$form[0].checkValidity()) {
        $form[0].reportValidity()
    } else {
        fetchFormDataAjax(url, function(html) {
            var formHTML = $(html).find('form#UserLoginForm')
            if (!formHTML.length) {
                window.location = baseurl + '/users/login'
            }
            $('body').append($('<div id="temp" style="display: none"/>').append(formHTML))
            var $tmpForm = $('#temp form#UserLoginForm')
            $tmpForm.find('#UserEmail').val(email)
            $tmpForm.find('#UserPassword').val(password)
            $tmpForm.submit()
        })
    }
}
</script>
