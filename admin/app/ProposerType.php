<?php

namespace App;

class ProposerType extends Model
{
    use Cacheable;

    const TYPE_IFRAME = 'iframe';
    const TYPE_EMBEDDED = 'embedded';
    const TYPE_POPUP = 'popup';
}
