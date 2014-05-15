<?php
class EditPostSourceJobTest extends AbstractTest
{
	public function testSaving()
	{
		$this->prepare();
		$this->grantAccess('editPostSource.own');
		$post = $this->assert->doesNotThrow(function()
		{
			return $this->runApi('a');
		});

		$this->assert->areEqual('a', $post->getSource());
		$this->assert->doesNotThrow(function() use ($post)
		{
			PostModel::getById($post->getId());
		});
	}

	public function testAlmostTooLongText()
	{
		$this->prepare();
		$this->grantAccess('editPostSource.own');
		$this->assert->doesNotThrow(function()
		{
			$this->runApi(str_repeat('a', Core::getConfig()->posts->maxSourceLength));
		});
	}

	public function testTooLongText()
	{
		$this->prepare();
		$this->grantAccess('editPostSource.own');
		$this->assert->throws(function()
		{
			$this->runApi(str_repeat('a', Core::getConfig()->posts->maxSourceLength + 1));
		}, 'Source must have at most');
	}

	public function testEmptyText()
	{
		$this->prepare();
		$this->grantAccess('editPostSource.own');
		$post = $this->assert->doesNotThrow(function()
		{
			return $this->runApi('');
		});
		$this->assert->areEqual('', $post->getSource());
	}

	public function testWrongPostId()
	{
		$this->prepare();
		$this->assert->throws(function()
		{
			Api::run(
				new EditPostSourceJob(),
				[
					JobArgs::ARG_POST_ID => 100,
					JobArgs::ARG_NEW_SOURCE => 'alohaa',
				]);
		}, 'Invalid post ID');
	}


	protected function runApi($text)
	{
		$post = $this->postMocker->mockSingle();
		return Api::run(
			new EditPostSourceJob(),
			[
				JobArgs::ARG_POST_ID => $post->getId(),
				JobArgs::ARG_NEW_SOURCE => $text
			]);
	}

	protected function prepare()
	{
		$this->login($this->userMocker->mockSingle());
	}
}
