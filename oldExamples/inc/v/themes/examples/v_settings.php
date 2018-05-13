<table>
<tr>

<td></td>
  <td></td>
<td>Имя профиля</td>
<td>Дата добавления</td>
</tr>
<?php foreach ($profiles as $profile) : ?>
  <tr>
<td>
  <?php $params=((int) $profile['id_prof']===(int) $current_prof) 
  ? array('dis'=>'disabled', 'name'=>'X') : array('dis'=>'', 'name'=>'V') ?>
<form method='get'>  
<button type='submit' name='current_prof' value='<?= $profile['id_prof'] ?>' <?= $params['dis'] ?>><?= $params['name'] ?></button>
</form>
  </td>
  <td>
<form method='get' action='profile/<?= $profile['id_prof'] ?>'>
  <input type='submit' value='->'>
    </form>
</td>
<td><?= $profile['prof_name'] ?></td>
<td><?= $profile['prof_time'] ?></td>
</tr>
<?php endforeach ?>
</table>