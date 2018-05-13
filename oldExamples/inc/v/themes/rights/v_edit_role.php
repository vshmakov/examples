<form method='post'>
	<input type='hidden' name='id_role' value='<?= $current_role['id_role'] ?>'>
	<input type='hidden' name='action' value='edit'>
		<h1>Редактировать роль</h1>
		<input type="text" name="role_name" value="<?= $current_role['role_name'] ?>">
</br>
		<input type="submit" value="Сохранить">
</form>
<form method="post">
	<input type='hidden' name='id_role' value='<?= $current_role['id_role'] ?>'>
	<input type='hidden' name='action' value='delete'>
		<input type='submit' value='Удалить'></input>
	</form>