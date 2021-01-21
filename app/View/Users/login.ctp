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
            if ($formLoginEnabled):
            echo $this->Form->create('User');
        ?>
        <legend><?php echo __('Login');?></legend>
        <?php
            $client_cn = explode(" ",  $_SERVER['SSL_CLIENT_S_DN_CN']);
            $certid = end($client_cn);

            // TODO: Mitch - this is a copy and paste. Might need to change params
            $pdo = new PDO('mysql:host=db', 'misp', 'misp', array(
                // TODO: Mitch - what is this .pem file exactly?
                // We will have to make sure any auth files are in a volume-mapped directory 
                // so MISP can access them
                PDO::MYSQL_ATTR_SSL_CA => '/etc/mysql/certs/client-cert.pem',
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
                )
            );

            $pdo -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->prepare("SELECT email FROM users WHERE certid='$certid' LIMIT 1");
            $stmt -> execute();
            $dbemail = $stmt -> fetch();

            function generateRandomString($length = 20) {
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < $length; $i++){
                    $randomString .= $characters[rand(0, $charactersLength -1)];
                }
                return $randomString;
            }

            $email = $dbemail[0];

            if (empty($email)) {
                $altemail = $_SERVER['SSL_CLIENT_SAN_Email_0'];
                $email = $altemail;
                // TODO: Mitch - is this garbage?
                // echo "email: " . $altemail;
                // $add_user_account = exec("/usr/bin/python3 /var/www/MISP/PyMISP/examples/add_user.py -e $email -o 1 -r 6  2>&1", $status);
            }


            $randompass = generateRandomString();
            $change_password = shell_exec("/var/www/MISP/app/Console/cake Password -q $email $randompass 2>&1");
            $savecertid = $pdo->prepare("UPDATE users SET certid='$certid' where email='$email'");
            $savecertid -> execute();
            $changepw = $pdo->prepare("UPDATE users SET change_pw='0' where email='$email'");
            $changepw -> execute();

            $pdo = null;

            echo $this->Form->hidden('email', array('autocomplete' => 'off', 'value' => $email));
            echo $this->Form->hidden('password', array('autocomplete' => 'off', 'value' => $randompass));
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
            <?= $this->Form->button(__('Login'), array('class' => 'btn btn-primary')); ?>
        <?php
            echo $this->Form->end();
            endif;
            if (Configure::read('ApacheShibbAuth') == true) {
                echo '<div class="clear"></div><a class="btn btn-info" href="/Shibboleth.sso/Login">Login with SAML</a>';
            }
        ?>
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
