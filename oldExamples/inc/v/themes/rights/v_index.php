<form method='post'>
<input type='hidden' name='id_user' value='<?= $current_user ?>'>  
<table>
<tr>
<td></td>
<td>Пользователь</td>
<td>Роль</td>
<td></td>
</tr>
<?php foreach($rows as $row) : ?>
<tr>
<td>
<?php if ($row['id_user']) : ?>
  <button type='submit' name='go' value='/rights/edit_user/<?= $row['id_user'] ?>'>-></button>
     <?php endif ?>
</td>
<td>
  <?php if ($row['id_user']) : ?>
        <?php if ((int) $row['id_user']!==(int) $current_user) : ?>
        <a href='?id_user=<?= $row['id_user'] ?>'><?=  $row['user_name'] ?></a>
        <?php else : ?>
        <?= $row['user_name'] ?>
        <?php endif ?>
<?php endif ?>
  </td>
    <td>
<?=  $row['role_name'] ?>
</td>
<td>
  <?php if ($row['id_role']) : ?>
<input type='radio' name='id_role' value='<?=  $row['id_role'] ?>'
        <?php if((int) $row['id_role']===(int) $checked_role) : ?>
           checked
           <?php endif ?>
  >
  <?php endif ?>
 </td>
</tr>
<?php endforeach ?>

</table>
<br></br>
<input type='submit' value='Сохранить'>
  </form>