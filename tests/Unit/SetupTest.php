<?php namespace Tests\Unit;

use Hampel\ContentBbCode\Setup;
use Tests\TestCase;

class SetupTest extends TestCase
{
	protected function setupClass()
	{
		$addOn = $this->app()->addOnManager()->getById('Hampel/ContentBbCode');

		return new Setup($addOn, $this->app());
	}

	public function test_upgrade_queues_the_file_clean_up_on_xenforo_2_3()
	{
		if (\XF::$versionId < 2030000)
		{
			$this->markTestSkipped('enqueuePostUpgradeCleanUp() exists from XenForo 2.3');
		}

		$this->fakesJobs();

		$stateChanges = [];
		$this->setupClass()->postUpgrade(2000370, $stateChanges);

		$this->assertJobQueued(\XF\Job\FileCleanUp::class, function ($job)
		{
			return $job['execute_data']['addon_id'] === 'Hampel/ContentBbCode';
		});
	}

	public function test_upgrade_queues_nothing_before_xenforo_2_3()
	{
		$this->fakesJobs();

		$versionId = \XF::$versionId;
		\XF::$versionId = 2021970;

		try
		{
			$stateChanges = [];
			$this->setupClass()->postUpgrade(2000370, $stateChanges);
		}
		finally
		{
			\XF::$versionId = $versionId;
		}

		$this->assertNoJobsQueued();
	}
}
