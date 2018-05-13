<h1>Архив попыток</h1>
<table>
<tr>
<td></td>
<td>Время начала</td>
<td>Время окончания</td>
<td>Всего примеров</td>
<td>Ошибок</td>
</tr>
<?php foreach ($archive as $try) : ?>
<tr>
<td>
<form method='get' action='history/<?= $try['id_try'] ?>'>
<input type='submit' value='->'>
</form>
</td>
<td><?= $try['time_start'] ?></td>
<td><?= $try['time_finish'] ?></td>
<td><?= $try['count'] ?></td>
<td><?= $try['count_errors'] ?></td>
</tr>
<?php endforeach ?>
</table>