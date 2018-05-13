<form method='get'>
  <h1>ИМЯ профиля</h1>
  <input type='text' name='prof_name' value='<?= $prof_name ?>'>
  <hr>
      
    <h2>Количество примеров</h2>
   <input type='text' name='number' value='<?= $number ?>' max length='3'>

<h2>Количество времени</h2>
      <input type='text' name='minutes' value='<?= $minutes ?>' maxlength='2'>:
      <input type='text' name='seconds' value='<?= $seconds ?>' maxlength='2'>
  <br><br>    
<h3>Сложение</h3>
  <table>
<tr>
<td>Минимальное слагаемое</td>
<td>
<input type='text' name='minadd' value='<?= $minadd ?>' maxlength='6'>
</td>
</tr>
<tr>
<td>Максимальное слагаемое</td>
  <td>
<input type='text' name='maxadd' value='<?= $maxadd ?>' maxlength='6'>
</td>
  </tr>
    <tr>
      <td>Процент примеров</td>
    <td>
<input type='text' name='addproc' value='<?= $addproc ?>' maxlength='3'>
  </td>
</tr>
  </table>
    
    <h3>Умножение</h3>
    <table>
    <tr>
<td>Минимальный множитель</td>
  <td>
<input type='text' name='minmult' value='<?= $minmult ?>' maxlength='6'>
</td>
</tr>
      <tr>
        <td>Максимальный множитель</td>
  <td>
<input type='text' name='maxmult' value='<?= $maxmult ?>' maxlength='6'>
</td>
        </tr>
      <tr>
        <td>Процент примеров</td>
    <td>
<input type='text' name='multproc' value='<?= $multproc ?>' maxlength='3'>
  </td>
</tr>
</table>
<br><br>
  <h3>Вычитание</h3>
  <table>
<tr>
<td>Минимальное вычитаемое</td>
<td>
<input type='text' name='minsub' value='<?= $minsub ?>' maxlength='6'>
</td>
</tr>
<tr>
<td>Максимальное уменьшаемое</td>
      <td>
<input type='text' name='maxsub' value='<?= $maxsub ?>' maxlength='6'>
</td>
      </tr>
    <tr>
      <td>Минимальная разность</td>
<td>
<input type='text' name='submin' value='<?= $submin ?>' maxlength='6'>
</td>
      </tr>
    <tr>
      <td>Процент примеров</td>
  <td>
<input type='text' name='subproc' value='<?= $subproc ?>' maxlength='3'>
  </td>
</tr>
    </table>
  
  <h3>Деление</h3>
      <table>
      <tr>
        <td>Минимальный делитель</td>
      <td>
<input type='text' name='mindiv' value='<?= $mindiv ?>' maxlength='6'>
</td>
</tr>
        <tr>
          <td>Максимальное делимое</td>
  <td>
<input type='text' name='maxdiv' value='<?= $maxdiv ?>' maxlength='6'>
</td>
</tr>
        <tr>
          <td>Минимальное частное</td>
  <td>
<input type='text' name='divmin' value='<?= $divmin ?>' maxlength='6'>
</td>
  </tr>
        <tr>
          <td>Процент примеров</td>
<td>
<input type='text' name='divproc' value='<?= $divproc ?>' maxlength='3'>
  </td>
</tr>
</table>
<br><br>
  <input type='checkbox' name='checkbox' value='new'> 
    Сохранить как новый профиль<br>
<input type='submit' value='Сохранить'>
  <button type='submit' name='action' value='delete_profile'>Удалить</button>
</form>