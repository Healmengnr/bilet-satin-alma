<?php
require_once 'config/config.php';

// Kullanıcı çıkış yap
logoutUser();

// Ana sayfaya yönlendir
setFlashMessage('success', 'Başarıyla çıkış yaptınız.');
redirect('/');
?>
