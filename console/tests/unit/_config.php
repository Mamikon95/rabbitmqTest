<?php
return [
	'id' => 'app-console',
	'class' => 'yii\console\Application',
	'basePath' => \Yii::getAlias('@console').'/tests',
	'runtimePath' => \Yii::getAlias('@console').'/tests/_output',
	'bootstrap' => [],
];