<h2>Статистика</h2>
<ul>
<li>Начало попытки: <?= $start ?></li>  
<li>Конец попытки: <?= $end ?></li>
<li>Всего примеров: <?= $count ?></li>
  <li>Ошибок: <?= $errors ?></li>
<li>В среднем сек/пример: <?= $e_t ?></li>
  </ul>

<h2>История</h2>
  <table>
<tr>
  <td>№</td>
  <td>Пример</td>
  <td>Ответ</td>
  </tr>
  <?php $_view=$errors ?>
    <?php foreach ($history as $example) : ?>
<tr>
<?php if (!$example['right']) : ?>
<tr>
<td></td>
<td><h4>Ошибка! (<?= $_view-- ?>)</h4></td>
<td></td>
</tr>
<?php endif ?>
<td><?= $example['number'] ?></td>
<td><?= $example['string'] ?></td>
<td><?=$example['answer'] ?></td>
</tr>
<?php endforeach ?>
</table>