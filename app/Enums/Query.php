<?php

namespace App\Enums;

enum Query
{
    case Include;
    case Exclude;
    case Only;
}