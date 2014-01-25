<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>
  <div class="checkout">
    <div id="checkout">
      <div class="checkout-content" style="display: block;">
		<form method="post" action="http://opencart/index.php?route=checkout/simple/confirm" enctype="multipart/form-data">
			<h2>Личные данные</h2>
			<span class="required">*</span> Имя<br />
			<input type="text" name="firstname" value="<?php echo $firstname; ?>" class="large-field" />
			<br />
			<?php if (isset($error['firstname'])) echo '<span class="error">'.$error['firstname'].'</span>'; ?>
			<br />
			<span class="required">*</span> Телефон<br />
			<input type="text" name="telephone" value="<?php echo $telephone; ?>" class="large-field" />
			<br />
			<?php if (isset($error['telephone'])) echo '<span class="error">'.$error['telephone'].'</span>'; ?>
			<br />
			<span class="required">*</span> Адрес<br />
			<input type="text" name="payment_address" value="<?php echo $payment_address; ?>" class="large-field" />
			<br />
			<?php if (isset($error['payment_address'])) echo '<span class="error">'.$error['payment_address'].'</span>'; ?>
			<br />
			Комментарий<br />
			<textarea name="comment" style="width: 300px;" ><?php echo $payment_address; ?></textarea>
			<br />
			<?php if (isset($error['comment'])) echo '<span class="error">'.$error['comment'].'</span>'; ?>
			<br />
			<input type="submit" value="Заказать" class="button" />
		</form>
      </div>
    </div>
  </div>
  <?php echo $content_bottom; ?></div>
<?php echo $footer; ?>