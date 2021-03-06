<?

namespace admin;

use Yii;
use yii\web\View;
use yii\base\Application;
use yii\base\BootstrapInterface;
use admin\models\Module;
use admin\behaviors\AccessBehavior;

class AdminModule extends \yii\base\Module implements BootstrapInterface {

    const VERSION = 0.91;
    const NAME = 'Studio451 CMS';
    const INSTALLED = true;//ВНИМАНИЕ! После установки измените переменную в значение "true"    
    
    public $settings;
    public $activeModules;    
    public $defaultRoute = 'a';

    public function behaviors() {

        if (\admin\AdminModule::INSTALLED) {
            return [
                'AccessBehavior' => [
                    'class' => AccessBehavior::className(),
                    'login_url' => '/user/login',
                    'rules' => 
                    [
                        'admin/rbac' => [['allow' => true, 'roles' => ['SuperAdmin'],],],
                        'admin/logs' => [['allow' => true, 'roles' => ['SuperAdmin'],],],
                        'admin/modules' => [['allow' => true, 'roles' => ['SuperAdmin'],],],
                        'admin/photos' => [['allow' => true, 'roles' => ['SuperAdmin'],],],
                        'admin/redactor' => [['allow' => true, 'roles' => ['SuperAdmin'],],],
                        'admin/session' => [['allow' => true, 'roles' => ['SuperAdmin'],],],
                        'admin/settings' => [['allow' => true, 'roles' => ['SuperAdmin'],],],
                        'admin/system' => [['allow' => true, 'roles' => ['SuperAdmin'],],],
                        'admin/tags' => [['allow' => true, 'roles' => ['SuperAdmin'],],],
                        'admin/translate' => [['allow' => true, 'roles' => ['SuperAdmin'],],],
                        'admin/user-permissions' => [['allow' => true, 'roles' => ['SuperAdmin'],],],
                        'admin/users' => [['allow' => true, 'roles' => ['SuperAdmin'],],],
                        'admin/dump' => [['allow' => true, 'roles' => ['SuperAdmin'],],],
                        'admin/user' => [['actions' => ['logout'], 'allow' => true, 'roles' => ['@'],],],
                    ],
                ],
            ];
        } else {
            return [];
        }
    }

    public function init() {
        parent::init();

        if (Yii::$app->cache === null) {
            throw new \yii\web\ServerErrorHttpException('Необходимо настроить компонент кэширования');
        }
        if (\admin\AdminModule::INSTALLED) {
            $this->activeModules = Module::findAllActive();

            $modules = [];
            foreach ($this->activeModules as $name => $module) {
                $modules[$name]['class'] = $module->class;
                if (is_array($module->settings)) {
                    $modules[$name]['settings'] = $module->settings;
                }
            }
            $this->setModules($modules);

            if (APP_CONSOLE == 'true') {
                //Секция для инициализации консольного приложения
            } else {
                define('LIVE_EDIT', !Yii::$app->user->isGuest && Yii::$app->session->get('admin_live_edit'));
            }
        }
    }

    public function bootstrap($app) {
        if (\admin\AdminModule::INSTALLED) {
            if (!$app->user->isGuest && strpos($app->request->pathInfo, 'admin') === false) {
                if (Yii::$app->user->can('admin')) {
                    $app->on(Application::EVENT_BEFORE_REQUEST, function () use ($app) {
                        $request = Yii::$app->request;
                        if (!$request->isAjax) {
                            $app->getView()->on(View::EVENT_BEGIN_BODY, [$this, 'renderToolbar']);
                        }
                    });
                }
            }
        }
    }

    public function renderToolbar() {
        $view = Yii::$app->getView();
        echo $view->render('@admin/views/layouts/toolbar.php');
    }
}