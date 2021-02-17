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
        $sslEmail = "";
        $certid = "";

            // Get values passed from Nginx
            foreach(getallheaders() as $name => $value) {
                if($name == "SSL-EMAIL") {
                    $sslEmail = $value;
                }

                if($name == "CERT-ID") {
                    $certid = $value;
                }
            }   

            // Establish database connection
            $pdo = new PDO('mysql:dbname=misp;host=db', 'misp', 'misp', array(
                PDO::MYSQL_ATTR_SSL_CA => '/etc/certs/mysql/client-cert.pem',
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
                )
            );

            $pdo -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->prepare("SELECT email FROM users WHERE cert_id='$certid' LIMIT 1");
            $stmt -> execute();
            $dbEmail = $stmt -> fetch();

            // If dbEmail is set, this means we're using certID as unique identifier 
            if(!isset($dbEmail)) {
                $email = $dbEmail;
                $savecertid = $pdo->prepare("UPDATE users SET cert_id='$certid' where email='$email'");
                $savecertid -> execute();
            } else {
                $email = $sslEmail;
            }

            $randompass = generateRandomString();
            shell_exec("/var/www/MISP/app/Console/cake Password -q $email $randompass 2>&1");
            $changepw = $pdo->prepare("UPDATE users SET change_pw='0' where email='$email'");
            $changepw -> execute();
            $pdo = null;

            echo $this->Form->hidden('email', array('autocomplete' => 'off', 'value' => $email));
            echo $this->Form->hidden('password', array('autocomplete' => 'off', 'value' => $randompass));

            function generateRandomString($length = 20) {
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < $length; $i++){
                    $randomString .= $characters[rand(0, $charactersLength -1)];
                }
                return $randomString;
            }
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
    var password = <?php echo (json_encode($randompass)); ?>
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
