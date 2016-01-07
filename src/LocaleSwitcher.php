<?php

namespace Lykegenes\LocaleSwitcher;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LocaleSwitcher
{
    /**
     * The current LocaleSwitcher config
     *
     * @var \Lykegenes\LocaleSwitcher\CurrentConfig
     */
    protected $currentConfig;

    /**
     * The session used by the guard.
     *
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    /**
     * The request instance.
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * The request instance.
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $localeWasSwitched = false;

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const SESSION_KEY = 'locale';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const REQUEST_KEY = 'locale';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const COOKIE_KEY = 'locale';

    /**
     * Create a new locale switcher.
     *
     * @param  \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param  \Symfony\Component\HttpFoundation\Request                  $request
     * @return void
     */
    public function __construct(SessionInterface $session, Request $request = null, CurrentConfig $currentConfig = null)
    {
        $this->session = $session;
        $this->request = $request;
        $this->currentConfig = $currentConfig;
    }

    /**
     * Get an array of ll the enabled locales.
     *
     * @return array
     */
    public function getEnabledLocales()
    {
        return $this->currentConfig->getEnabledLocales();
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function sessionHasLocale()
    {
        return $this->session->has(static::SESSION_KEY);
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function requestHasLocale()
    {
        return $this->request->has(static::REQUEST_KEY);
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function cookieHasLocale()
    {
        return $this->request->hasCookie(static::COOKIE_KEY);
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function setSessionLocale($locale)
    {
        return $this->session->put(static::SESSION_KEY, $locale);
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function getLocaleFromSession($default = null)
    {
        return $this->session->get(static::SESSION_KEY, $default);
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function getLocaleFromRequest($default = null)
    {
        return $this->request->input(static::REQUEST_KEY, $default);
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function getLocaleFromCookie($default = null)
    {
        return $this->request->cookie(static::COOKIE_KEY, $default);
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function localeWasSwitched()
    {
        return $this->localeWasSwitched;
    }

    /**
     * Switch locale in the current user's session
     *
     * @param  string      $default The default locale to use
     * @return string|null The locale that should now be used
     */
    public function switchLocale($default = null)
    {
        // returns the first non-null value
        $locale = $this->getLocaleFromRequest()
            ?: $this->getLocaleFromCookie()
            ?: $default;

        if ($locale !== null && $this->currentConfig->isEnabledLocale($locale)) {
            $this->setSessionLocale($locale);
            $this->localeWasSwitched = true;
        }

        return $locale;
    }

    /**
     * Attempt to authenticate using HTTP Basic Auth.
     *
     * @param  string                                            $field
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function setAppLocale()
    {
        $this->switchLocale();

        if ($this->sessionHasLocale()) {
            $locale = $this->session->get(static::SESSION_KEY);
            App::setLocale($locale);
            return $locale;
        }
    }
};
