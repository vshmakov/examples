<form method='post'>
<input type='hidden' name='id_page' value='<?= $current_page['id_page'] ?>'>
	<input type='hidden' name='action' value='edit'>
	<h1>Редактировать страницу</h1>
	<table>
		<tr>
			<td>Название страницы</td>
			<td>Заголовок страницы</td>
			<td>Контроллер</td>
			<td>Требуемая привелегия</td>
		</tr>
		<tr>
			<td>
				<input type="text" name="title" value="<?= $current_page['title'] ?>">
			</td>
			<td>
				<input type="text" name="page_name" value="<?= $current_page['page_name'] ?>">
			</td>
			<td>
				<select name="id_con">
					<option  value="no">- - - -</option>
					<?php foreach($controllers as $controller) : ?>
					<option value="<?= $controller['id_con'] ?>"
<?php if ($current_page['id_con']==$controller['id_con']) : ?>
 selected
<?php endif ?>?>
><?= $controller['con_name'] ?>
					</option>
					<?php endforeach ?>
				</select>
			</td>
			<td>
				<select name="id_priv">
					<option value="no">- - - -</option>
					<?php foreach($privs as $priv) : ?>
					<option value="<?= $priv['id_priv'] ?>"
						<?php if ($current_page['id_priv']==$priv['id_priv']) : ?>
						selected
						<?php endif ?>?>
><?= $priv['priv_name'] ?>
					</option>
					<?php endforeach ?>
				</select>
			</td>
		</tr>
	</table>
	<br></br>
		<input type="submit" value="Сохранить">
</form>
<form method="post">
	<input type='hidden' name='id_page' value='<?= $current_page['id_page'] ?>'>
	<input type='hidden' name='action' value='delete'>
		<input type='submit' value='Удалить'></input>
	</form>
