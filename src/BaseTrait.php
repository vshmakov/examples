<?php

namespace App;

trait BaseTrait {
private function dt($dt) {
return DT::createFromDT($dt);
}

private function dts($s) {
return DT::createFromTimestamp($s);
}

}