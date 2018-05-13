<form method='post'>
<input type='hidden' name='action' value='privs2roles'>
<input type='hidden' name='id_role' value='<?= $current_role ?>'>
<table>
<tr>
<td></td>
<td>Роль</td>
<td>Привелегия</td>
<td></td>
<td></td>
</tr>
<?php foreach($rows as $key=>$row) : ?>
<tr>
<td>
<?php if ($row['id_role']) : ?>
<button type='submit' name='go' value='/rights/edit_role/<?= $row['id_role'] ?>'>-></button>
<?php endif ?>
</td>
<td>
<?php if((int) $row['id_role']!==(int) $current_role) : ?>
<a href='?id_role=<?= $row['id_role'] ?>'><?= $row['role_name'] ?></a>
<?php else: ?>
<?= $row['role_name'] ?>
<?php endif ?>
</td>
<td>
<?= $row['priv_name'] ?>
</td>
<?php if ($row['id_priv']) :?>
<td>
  <input type='checkbox' name='checkbox<?= $key ?>' value='<?= $row['id_priv'] ?>'
<?php if (in_array($row['id_priv'], $checked_privs)) : ?>
checked
<?php endif ?>
></td>
<td>
<button type='submit' name='go' value='/rights/edit_priv/<?= $row['id_priv'] ?>'><-</button>

</td>
<?php else : ?>
<td></td>?>
<td></td>
><?php endif ?>
</tr>
<?php endforeach ?>

</table>
<br></br>
<input type='submit' value='Сохранить'>
</form>
<form method='post'>
<input type='hidden' name='action' value='add'>
<h1>Создать роль/привелегию</h1>
<table>
<tr>
<td>
название
</td>
<td>
Роль/привелегия
</td>
</tr>
<tr>
<td>
<input type='text' name='name'>
</td>
<td>
<select name='type'>
<option value='no'>- - - -</option>
<option value='role'>Роль</option>
<option value='priv'>Привелегия</option>
</select>
</td>
</tr>
</table>
<br></br>
<input type='submit' value='Добавить'>
</form>