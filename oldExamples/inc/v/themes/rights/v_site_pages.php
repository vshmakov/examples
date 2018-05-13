<table>
<tr>
<td></td>
    <td>Название страницы</td>
<td>Заголовок страницы</td>
<td>Требуемая привелегия</td>
</tr>
<tr>
<td>
<form method="get" action="edit_page/<?= $site['id_page'] ?>">
<input type="submit" value="->">
</form>
</td>
<td>
<h1>
<b>SITE</b>
</h1>
</td>
<td>
<?=  $site['page_name'] ?>
</td>
<td></td>
</tr>
<?php foreach ($site_pages as $controller) : ?>
<tr>
<td>
<form method="get" action="edit_page/<?= $controller['id_page'] ?>">
<input type="submit" value="->">
</form>
</td>
<td>
<h3>
<?=  $controller['title'] ?>
</h3>
</td>
<td>
<?=  $controller['page_name'] ?>
</td>
<td>
<?=  $controller['priv_name'] ?>
</td>
</tr>
<?php foreach ($controller['ch_pages'] as $page) : ?>
<tr>
<td>
<form method="get" action="edit_page/<?= $page['id_page'] ?>">
<input type="submit" value="->">
</form>
</td>
<td>
<?=  $page['title'] ?>
</td>
<td>
<?=  $page['page_name'] ?>
</td>
<td>
<?=  $page['priv_name'] ?>
</td>
</tr>
<?php endforeach ?>
<?php endforeach ?>
</table>
<br><br>
<form method='post'>
<h1>Создать страницу</h1>
<table>
<tr>
<td>Название страницы</td>
<td>Заголовок страницы</td>
<td>Контроллер</td>
<td>Требуемая привелегия</td>
</tr>
<tr>
<td><input type="text" name="title"></td>
<td><input type="text" name="page_name"></td>
<td>
<select name="id_con">
<option  value="no">- - - -</option>
<?php foreach($controllers as $controller) : ?>
<option value="<?= $controller['id_con'] ?>"><?= $controller['con_name'] ?>
</option>
<?php endforeach ?>
</select>
</td>
<td><select name="id_priv">
<option value="no">- - - -</option>
<?php foreach($privs as $priv) : ?>
<option value="<?= $priv['id_priv'] ?>"><?= $priv['priv_name'] ?>
</option>
<?php endforeach ?>
</select></td>
</tr>
</table>
<br></br>
<input type="submit" value="Добавить">
</form>