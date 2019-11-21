<?PHP

class Battle
{
	protected $immuneSystem;
	protected $infection;

	public function		createArmies(array $immuneSystem, array $infection)
	{
		$this->immuneSystem = $immuneSystem;
		$this->infection = $infection;
	}

	/**
	 * Integer increase of units.
	 */
	public function		boost(string $army, int $by): bool
	{
		if (!isset($this->$army))
			return false;
		for ($id = 0; $id < count($this->$army); $id++)
			$this->$army[$id]['attack'] += $by;
		return true;
	}

	public function		finishWar()
	{
		while ($this->immuneSystem && $this->infection)
		{
			if (!$this->targetSelection())
				return ;
			$this->attack();
		}
		return $this->returnTotalUnits();
	}

	protected function	returnTotalUnits()
	{
		$total = [
			'immuneSystem'	=> 0,
			'infection'		=> 0,
			'winner'		=> 'draw',
		];

		foreach ($this->immuneSystem as $group)
			$total['immuneSystem'] += $group['units'];
		foreach ($this->infection as $group)
			$total['infection'] += $group['units'];
		if ($total['infection'] !== $total['immuneSystem'])
			$total['winner'] = $total['infection'] > $total['immuneSystem'] ? 'infection' : 'immuneSystem';
		return $total;
	}

	protected function	findTarget($attackingArmy, $defendingArmy, $key): bool
	{
		$group = &$this->$attackingArmy[$key];

		foreach ($this->$defendingArmy as $k => $t)
		{
			if ($t['targeted'] || in_array($group['attack-type'], $t['immune'], true))
				continue;
		
			$double = false;
			// Default damage.
			$damage = $group['epower'];
			// Double the damage if the target group has a weakness to
			// attacking's group type of attack.
			if (in_array($group['attack-type'], $t['weak'], true)) {
				$damage *= 2;
				$double = true;
			}

			if (!$group['target'] || $group['target']['damage'] < $damage)
			{
				$group['target'] = ['target-key' => $k, 'damage' => $damage, 'double' => $double];
			}
			elseif ($group['target']['damage'] === $damage)
			{
				$prevTarget = $this->$defendingArmy[$group['target']['target-key']];
				if ($prevTarget['epower'] < $t['epower'])
					$group['target'] = ['target-key' => $k, 'damage' => $damage, 'double' => $double];
				elseif ($prevTarget['epower'] === $t['epower']
					&& $prevTarget['initiative'] < $t['initiative'])
					$group['target'] = ['target-key' => $k, 'damage' => $damage, 'double' => $double];
			}
		}
		if (null !== $group['target'])
		{
			$this->$defendingArmy[$group['target']['target-key']]['targeted'] = true;
			return true;
		}
		return false;
	}

	public function		targetSelection()
	{
		$this->setPowerResetTarget('immuneSystem');
		$this->setPowerResetTarget('infection');

		return $this->targetSelectionHelper('immuneSystem', 'infection')
			|| $this->targetSelectionHelper('infection', 'immuneSystem');
	}

	private function	targetSelectionHelper($attacker, $defender)
	{
		$didFindTarget = false;
		$army = $this->$attacker;

		while ($army)
		{
			$key = $this->getKey($army);
			if ($this->findTarget($attacker, $defender, $key))
				$didFindTarget = true;
			unset($army[$key]);
		}
		return $didFindTarget;
	}

	public function		attack()
	{
		$armies = array_merge($this->immuneSystem, $this->infection);

		usort($armies, function ($a, $b) {
			return $b['initiative'] - $a['initiative'];
		});

		for ($i = 0; $i < count($armies); $i++)
		{
			$attackerGroup = $armies[$i]['group'];
			$attackerId = $armies[$i]['id'];
			if (!isset($this->$attackerGroup[$attackerId]) || !$this->$attackerGroup[$attackerId]['target'])
				continue;
			$attacker = $this->$attackerGroup[$attackerId];
			$defender = $attacker['group'] === "infection" ? 'immuneSystem' : 'infection';
			$key = $attacker['target']['target-key'];
			$damage = (int) ($attacker['units'] * $attacker['attack'] * ($attacker['target']['double'] ? 2 : 1) / $this->$defender[$key]['hit-points']);

			if (0 >= ($this->$defender[$key]['units'] -= $damage))
				unset($this->$defender[$key]);
		}
	}

	protected function	getKey($army, $step = 0)
	{
		$ret = null;

		foreach ($army as $key => $group)
		{
			if ($group['epower'] > $step)
			{
				$step = $group['epower'];
				$ret = $key;
			}
		}
		return $ret;
	}

	protected function	setPowerResetTarget($army)
	{
		foreach ($this->$army as &$group)
		{
			$group['epower'] = $group['attack'] * $group['units'];
			$group['target'] = null;
			$group['targeted'] = false;
			unset($group);
		}
	}

	public function		print()
	{
		echo "Immune System:\n";
		foreach ($this->immuneSystem as $key => $groups)
			echo "Group ",($key + 1)," contains ",($groups['units'])," units\n";
		
		echo "Infection:\n";
		foreach ($this->infection as $key => $groups)
			echo "Group ",($key + 1)," contains ",($groups['units'])," units\n";
	}
}
