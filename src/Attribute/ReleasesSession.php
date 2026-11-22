<?php

namespace App\Attribute;

use Attribute;

/**
 * The action only reads the session (who is logged in): the session is saved, and its lock released, before the
 * action runs (App\EventListener\ReleaseSessionListener).
 *
 * Sessions are locked (PdoSessionHandler, LOCK_ADVISORY), so the requests of one member run one after the other. For
 * the pictures, widgets and counters a page asks for (dozens at once) that would mean waiting in line for nothing:
 * they change nothing in the session. An action that writes to the session after all opens it again, with its lock.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class ReleasesSession
{
}
