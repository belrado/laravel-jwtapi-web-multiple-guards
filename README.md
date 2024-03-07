
# Laravel Multiple guards & Swagger

## Api JwtAuth Guard

1.  composer require tymon/jwt-auth
2.  config > auth.php > defaults api 설정
    > 'defaults' => ['guard' => 'api', 'passwords' => 'users',]
3.  config > auth.php > guards > api 설정
    > 'api' => ['driver' => 'jwt','provider' => 'users','hash' => false,]
4.  config > app.php > providers 추가
    > 'providers' => [ ... Tymon\JWTAuth\Providers\LaravelServiceProvider::class, ]
5.  미들 웨어 생성 jwt 접근관리, 새로 고침 관리
    > php artisan make::middleware JwtAuthenticate, JwtRefresh
    > 
    > app/Http/Middleware/JwtAuthenticate.php jwt 접근 관리
    >
    > app/Http/Middleware/JwtRefresh.php jwt 새로 고침 관리
6.  config > app.php > aliases 에 추가
    > 'aliases' => [ ... 
    > 
    > 'JWTAuth' => Tymon\JWTAuth\Facades\JWTAuth::class,
    > 
    > 'JWTFactory' => Tymon\JWTAuth\Facades\JWTFactory::class, ...]
7.  app/Http/Kernel.php >  protected $routeMiddleware 에 추가
    > jwtAuth' => \App\Http\Middleware\JwtAuthenticate::class,
    > 
    > 'jwtRefresh' => \App\Http\Middleware\JwtRefresh::class,

-   routes/api.php
    > Route::middleware('jwtAuth')->group(function() { ...

## Web Auth Guard

config > auth.php > guards > web guard 작성 (기본으로 작성되어있음)
- 웹라우트를 사용할경우 route/web.php > 라우트 미들웨어에 auth:web 적용 
    > Route::middleware(['auth:web'])->prefix('xxxx')->group(function() {});


### web 라우터는 웹사이트에서 사용 api 라우터는 외부 사이트나 앱 또는 내부 웹에서 ajax 전송시 사용

## Api 스웨거 적용

1. 설치
    > composer require darkaonline/l5-swagger
2. 설정 파일과 뷰 파일 생성
    > php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
3. config/app.php provider 등록
    > providers' => [ ... L5Swagger\L5SwaggerServiceProvider::class, ...]
4. 최상위 컨트롤러에 해당 프로젝트의 명세서 정보 등을 입력
5. 사용하는 api에 Annotation 적용
    > https://zircote.github.io/swagger-php/reference/annotations.html
6. Api 문서 생성
    > php artisan l5-swagger:generate
7. 문서 접근
   > /api/documentation
