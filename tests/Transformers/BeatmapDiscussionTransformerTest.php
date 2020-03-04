<?php

/**
 *    Copyright (c) ppy Pty Ltd <contact@ppy.sh>.
 *
 *    This file is part of osu!web. osu!web is distributed with the hope of
 *    attracting more community contributions to the core ecosystem of osu!.
 *
 *    osu!web is free software: you can redistribute it and/or modify
 *    it under the terms of the Affero GNU General Public License version 3
 *    as published by the Free Software Foundation.
 *
 *    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
 *    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *    See the GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tests\Transformers;

use App\Models\Beatmapset;
use App\Models\User;
use Tests\TestCase;

class BeatmapDiscussionTransformerTest extends TestCase
{
    protected $deletedBeatmapDiscussion;

    /**
     * @dataProvider groupsDataProvider
     */
    public function testWithOAuth($groupIdentifier)
    {
        $viewer = factory(User::class)->states($groupIdentifier)->create();
        $this->actAsScopedUser($viewer);

        $json = json_item($this->deletedBeatmapDiscussion, 'BeatmapDiscussion');

        $this->assertEmpty($json);
    }

    /**
     * @dataProvider groupsDataProvider
     */
    public function testWithoutOAuth($groupIdentifier, $visible)
    {
        $viewer = factory(User::class)->states($groupIdentifier)->create();
        auth()->setUser($viewer);

        $json = json_item($this->deletedBeatmapDiscussion, 'BeatmapDiscussion');

        if ($visible) {
            $this->assertNotEmpty($json);
        } else {
            $this->assertEmpty($json);
        }
    }

    public function groupsDataProvider()
    {
        return [
            ['admin', true],
            ['bng', false],
            ['gmt', true],
            ['nat', true],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $mapper = factory(User::class)->create();
        $beatmapset = factory(Beatmapset::class)->states('with_discussion')->create([
            'user_id' => $mapper->getKey(),
        ]);

        $this->deletedBeatmapDiscussion = $beatmapset->beatmapDiscussions()->first();
        $this->deletedBeatmapDiscussion->update(['deleted_at' => now()]);
    }
}
