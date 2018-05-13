<form method='post'>
	<input type='hidden' name='id_priv' value='<?= $current_priv['id_priv'] ?>'>
	<input type='hidden' name='action' value='edit'>
		<h1>Редактировать привелегию</h1>
		<input type="text" name="priv_name" value="<?= $current_priv['priv_name'] ?>">
</br>
		<input type="submit" value="Сохранить">
</form>
<form method="post">
	<input type='hidden' name='id_priv' value='<?= $current_priv['id_priv'] ?>'>
	<input type='hidden' name='action' value='delete'>
		<input type='submit' value='Удалить'>
	</form>