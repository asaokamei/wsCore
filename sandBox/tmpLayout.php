<?php /** @var $_v \wsModule\Templates\Template */ ?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $_v->title; ?></title>
</head>
<body>
<div id="content"><?= $_v->get( 'content' ); ?></div>
<?php if( $_v->block ) : ?>
<div id="block"><?= $_v->get( 'block' ); ?></div>
<?php endif; ?>
</body>
</html>