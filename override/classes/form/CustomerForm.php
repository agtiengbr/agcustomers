<?php
class CustomerForm extends CustomerFormCore
{
    public function submit()
    {
        // Pre-validate age limits if configured in agcustomers
        if (isset($this->formFields['birthday'])) {
            $birthdayStr = $this->formFields['birthday']->getValue();
            // Load module configuration directly
            $raw = Configuration::get('AGCUSTOMERS_CONFIG');
            if ($raw) {
                $opts = @unserialize($raw);
                if (is_array($opts) && isset($opts['config']['customer'])) {
                    $minAge = (int)($opts['config']['customer']['min_age'] ?? 0);
                    $maxAge = (int)($opts['config']['customer']['max_age'] ?? 0);

                    if ($birthdayStr && preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdayStr)) {
                        list($y, $m, $d) = array_map('intval', explode('-', $birthdayStr));
                        if (checkdate($m, $d, $y)) {
                            $today = new \DateTimeImmutable('today');
                            $bday  = new \DateTimeImmutable(sprintf('%04d-%02d-%02d', $y, $m, $d));
                            $age   = (int)$bday->diff($today)->y;

                            // Translator may not be available here; fallback to Context
                            $translator = Context::getContext()->getTranslator();
                            if ($minAge > 0 && $age < $minAge) {
                                $this->errors[] = $translator->trans('You must be at least %min% years old.', ['%min%' => $minAge], 'Modules.Agcustomers.Shop');
                                // Reformat birthday to locale for redisplay
                                $obj = \DateTime::createFromFormat('Y-m-d', $birthdayStr);
                                if ($obj) {
                                    $this->formFields['birthday']->setValue($obj->format(Context::getContext()->language->date_format_lite));
                                }
                                return false;
                            }

                            if ($maxAge > 0 && $age > $maxAge) {
                                $this->errors[] = $translator->trans('The maximum allowed age is %max% years.', ['%max%' => $maxAge], 'Modules.Agcustomers.Shop');
                                // Reformat birthday to locale for redisplay
                                $obj = \DateTime::createFromFormat('Y-m-d', $birthdayStr);
                                if ($obj) {
                                    $this->formFields['birthday']->setValue($obj->format(Context::getContext()->language->date_format_lite));
                                }
                                return false;
                            }
                        }
                    }
                }
            }
        }

        $ok = parent::submit();
        if (!$ok && isset($this->formFields['birthday'])) {
            $obj = \DateTime::createFromFormat('Y-m-d', $this->formFields['birthday']->getValue());
            if ($obj) {
                $this->formFields['birthday']->setValue(
                    $obj->format(Context::getContext()->language->date_format_lite)
                );
            }
        }

        return $ok;
    }
}
