<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReactShellTest extends TestCase
{
    public function test_serves_the_react_shell_at_the_root(): void
    {
        // The compiled assets are irrelevant here: what matters is that Laravel
        // returns the mount point the React bundle attaches to.
        $this->withoutVite();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('id="app"', escape: false);
    }
}
