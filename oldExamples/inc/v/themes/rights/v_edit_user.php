<form method='post'>
	<input type='hidden' name='id_user' value='<?= $current_user['id_user'] ?>'>
	<input type='hidden' name='action' value='edit'>
		<h1>Редактирование информации пользователя</h1>
		<input type='text' name='login' value='<?= $current_user['login'] ?>'><br>
      <input type="text" name="user_name" value="<?= $current_user['user_name'] ?>">
</br>
		<input type="submit" value="Сохранить">
</form>
<form method="post">
	<input type='hidden' name='id_user' value='<?= $current_user['id_user'] ?>'>
	<input type='hidden' name='action' value='delete'>
		<input type='submit' value='Удалить'>
	</form>