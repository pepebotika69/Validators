<?php

namespace Validators;

use Yii;
use yii\validators\Validator;
use yii\base\Model;
use yii\web\View;

/**
 * Class OgrnValidator
 * Проверяет поле ОГРН
 *
 * @package app\components\validators
 */
class OgrnValidator extends Validator {
	private const OGRN_MAX = 15;
	private const OGRN_MIN = 13;

	public $type;

	private static $messages = [
		'general' => 'Поле «{label}» состоит либо из 13 либо из 15 цифр.',
		'noIp' => 'Поле «{label}» состоит из 13 цифр.',
		'onlyIp' => 'Поле «{label}» состоит из 15 цифр.'
	];

	/**
	 * @param Model $model
	 * @param string $attribute
	 */
	public function validateAttribute($model, $attribute): void {
		$length = strlen((int)$model->$attribute);
		if (!empty($model->$attribute)) {
			if ($this->type === 'noIp' && self::OGRN_MIN !== $length) {
				$this->addError(
					$model,
					$attribute,
					$this->getErrorMessage($model->getAttributeLabel($attribute), 'noIp')
				);
			} elseif ($this->type === 'onlyIp' && self::OGRN_MAX !== $length) {
				$this->addError(
					$model,
					$attribute,
					$this->getErrorMessage($model->getAttributeLabel($attribute), 'onlyIp')
				);
			} elseif (!(self::OGRN_MIN === $length || self::OGRN_MAX === $length)) {
				$this->addError(
					$model,
					$attribute,
					$this->getErrorMessage($model->getAttributeLabel($attribute), 'general')
				);
			}
		}
	}

	/**
	 * @param Model $model
	 * @param string $attribute
	 * @param View $view
	 * @return string|null
	 */
	public function clientValidateAttribute($model, $attribute, $view): ?string {
		$errorMessageNoIp = $this->getErrorMessage($model->getAttributeLabel($attribute), 'noIp');
		$errorMessageOnlyIp = $this->getErrorMessage($model->getAttributeLabel($attribute), 'onlyIp');
		$errorMessageGeneral = $this->getErrorMessage($model->getAttributeLabel($attribute), 'general');
		$ogrnMin = self::OGRN_MIN;
		$ogrnMax = self::OGRN_MAX;
		$type = $this->type ?? 'null';

		return <<<JS
			const ogrn = Number(value), 
		  		  length = ogrn.toString().length;
			if ('' !== value) {
				if ('noIp' === '$type' && $ogrnMin !== length) {
					messages.push('$errorMessageNoIp');
				} else if ('onlyIp' === '$type' && $ogrnMax !== length) {
					messages.push('$errorMessageOnlyIp');
				} else if (!($ogrnMin === length || $ogrnMax === length)) {
					messages.push('$errorMessageGeneral');
				}
			}
JS;
	}

	/**
	 * @param $label
	 * @param $condition
	 * @return string
	 */
	private function getErrorMessage($label, $condition): string {
		return Yii::$app->i18n->format(
			self::$messages[$condition],
			['label' => $label],
			'ru_RU'
		);
	}
}
