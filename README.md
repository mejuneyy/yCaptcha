# yCaptcha
a simple php lib for captcha

一个简单的验证码类,使用简单,无命名空间,不需要加载字体,引用即可用

##example:
require('ycaptcha.php');

$captcha = new Ycaptcha();

$captcha->build();

//查看验证码中的字符

//echo $captcha->captcha_key;

//输出图片

echo $captcha->showCaptcha();
