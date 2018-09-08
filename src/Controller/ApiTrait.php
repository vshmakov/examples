<?php

namespace App\Controller;

use App\Entity\Attempt;

trait ApiTrait
{
    private function processAttempts($as)
    {
        $attR = $this->em()->getRepository(Attempt::class);

        foreach ($as
 as $att) {
            $ks = 'title addTime solvedTime solvedExamplesCount errorsCount rating finishTime';
            $row = createNumArr(getKeysFromEntity($ks, $att->setER($attR)));

            $row[0] = sprintf('<a href="%s">%s</a>', $this->generateUrl('attempt_show', ['id' => $att->getId()]), $row[0]);
            $row[1] = ''.$row[1];
            $row[2] = sprintf('%s из %s (%s сек/пример)', $row[2]->minSecFormat(), $att->getMaxTime()->minSecFormat(), $att->getAverSolveTime()->getTimestamp());
            $row[3] = sprintf('%s из %s', $row[3], $att->getExamplesCount());
            $o = $row[5];
            $c = 'red';

            if (3 == $o) {
                $c = 'orange';
            }

            if (4 == $o) {
                $c = 'yellow';
            }

            if (5 == $o) {
                $c = 'green';
            }
            $row[5] = sprintf('<span style="background: %s;">%s</span>', $c, $o);

            extract(makeVarKeys($row, 'r'));
            $d[] = [$r0, $r1, "$r6", $r2, $r3, $r4, $r5];
        }

        return $d;
    }
}
