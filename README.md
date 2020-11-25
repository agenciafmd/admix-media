## [WIP] F&MD - Media

[![Downloads](https://img.shields.io/packagist/dt/agenciafmd/admix-media.svg?style=flat-square)](https://packagist.org/packages/agenciafmd/admix-media)
[![Licença](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

- Uploads de arquivos e imagens de forma descomplicada
- Geração das miniaturas em pjpg e webp

## Instalação

```shell script
composer require agenciafmd/admix-media:dev-master
```

É **extremamente** importarte termos esses aplicativos instalados.

```
sudo apt-get install jpegoptim
sudo apt-get install optipng
sudo apt-get install pngquant
sudo npm install -g svgo
sudo apt-get install gifsicle
sudo apt-get install webp
```

Execute a migração

```shell script
php artisan migrate
```

Uma vez que usamos como base o [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary) o processo de instalação é quase o mesmo.

```php
use Agenciafmd\Media\Traits\MediaTrait;
use Spatie\MediaLibrary\HasMedia;

class YourModel extends Model implements HasMedia
{
    use MediaTrait;

    protected $guarded = [
        'media',
    ];
}
```

## Configuração

Por padrão, as configurações do pacote são:

- manter o redimensionamento das imagens na fila
- otimizar todas as imagens para 95% da qualidade enviada
- utilizar as views fornecidas pelo proprio pacote na renderização das imagens

Se for preciso, você pode customizar estas configurações

```shell script
php artisan vendor:publish --force --tag=admix-media:config
```

## Redimensionamentos

É importante sabermos que o pacote cria os thumbs que serão utilizados na nossa aplicação.
Para configurarmos estes tamanhos, é preciso **criar** o arquivo de configuração (upload-configs.php) em cada um de nossos pacotes.
Sei que parece complicado, mas na prática é mais facil.

```php
return [
    'customer' => [ //nome da model em minusculo
        'image' => [ //nome do campo em minusculo
            'label' => 'imagem', //label do campo
            'multiple' => false, //se permite o upload multiplo
            'sources' => [
                [
                    'conversion' => 'min-width-1366',
                    'media' => '(min-width: 1366px)',
                    'width' => 938,
                    'height' => 680,
                ],
                [
                    'conversion' => 'min-width-1280',
                    'media' => '(min-width: 1280px)',
                    'width' => 776,
                    'height' => 565,
                ],
            ],
            // esta solução, permite mais de um campo na descrição da imagem
            // é muito util para galeria de imagens que possuem mais 
            // de uma simples descrição, como no exemplo abaixo.
            // Lembrando que este item é opcional e deve ser 
            // suprimido sempre que possivel.
            'meta' => [
                [
                    'label' => 'tipo',
                    'name' => 'type',
                    'options' => [
                        'Plantas Baixas',
                        'Implantações',
                    ],
                ],
                [
                    'label' => 'título',
                    'name' => 'title',
                ],
            ],
        ],
    ],
];
```

## Admix

Para colocarmos o campo de upload no nosso pacote, vamos até o `form.blade.php`
Por convenção, manteremos o plural do campo, sempre que for upload múltiplo.

```blade
{{ Form::bsImage('Imagem', 'image', $model) }}

{{ Form::bsImages('Imagens', 'images', $model) }}

{{ Form::bsMedia('Arquivo', 'file', $model) }}

{{ Form::bsMedias('Arquivos', 'files', $model) }}
```

Ou o modo "lazy" onde o `user` é o nome da nossa `model` em minusculo

```blade
@foreach(config('upload-configs.user') as $field => $upload)
    @if($upload['multiple'])
        {{ Form::bsImage($upload['label'], $field, $model) }}
    @else
        {{ Form::bsImages($upload['label'], $field, $model) }}
    @endif
@endforeach
```

Não podemos esquecer do nosso querido Request

```php
class YourRequest extends FormRequest
{
    public function rules()
    {
        return [
            'media' => [
                'array',
                'nullable',
            ],
        ];
    }
}
```

## Frontend

Como uma das idéias é conseguirmos otimizar o processo de mostrar as imagens, temos algumas facilidades no pacote.

| Metodos | Descrição |
| ---- | ---- |
| `$model->picture()` | retorna a tag \<picture\> com os sources |
| `$model->pictures()` | retorna um array de tags \<picture\> com os sources |
| `$model->fancyPicture()` | retorna a tag \<picture\> com os sources e o link de zoom com o fancybox |
| `$model->fancyPictures()` | retorna um array de tags \<picture\> com os sources e os link de zoom com o fancybox |
| `$model->file()` | retorna o path do arquivo |
| `$model->files()` | retorna um array com os paths dos arquivos |

Ex.
```blade
{{ $model->picture('image') }}
```

```html
<picture>
    <source media="(min-width: 1366px)"
            srcset="/storage/81/conversions/irineu-junior-200810171044000-min-width-1366-webp.webp, /storage/81/conversions/irineu-junior-200810171044000-min-width-1366-webp@2x.webp 2x">
    <source media="(min-width: 1366px)"
            srcset="/storage/81/conversions/irineu-junior-200810171044000-min-width-1366.jpg, /storage/81/conversions/irineu-junior-200810171044000-min-width-1366@2x.jpg 2x">
    <source media="(min-width: 1280px)"
            srcset="/storage/81/conversions/irineu-junior-200810171044000-min-width-1280-webp.webp, /storage/81/conversions/irineu-junior-200810171044000-min-width-1280-webp@2x.webp 2x">
    <source media="(min-width: 1280px)"
            srcset="/storage/81/conversions/irineu-junior-200810171044000-min-width-1280.jpg, /storage/81/conversions/irineu-junior-200810171044000-min-width-1280@2x.jpg 2x">
    <img src="data:image/png;base64, R0lGODlhAQABAIAAAMLCwgAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw=="
         loading="lazy"
         class="img-fluid"
         title="irineu-junior-200810171044000.jpg"
         alt="irineu-junior-200810171044000.jpg"></picture>
```

```blade
@foreach($model->fancyPictures() as $picture)
    {{ $picture }}
@endforeach
```

```html
<a href="/storage/82/irineu-junior-200810171121000.jpg"
   data-fancybox="images"
   data-caption="irineu-junior-200810171121000.jpg"
   class="">
    <picture>
        <source media="(min-width: 1366px)"
                srcset="/storage/82/conversions/irineu-junior-200810171121000-min-width-1366-webp.webp, /storage/82/conversions/irineu-junior-200810171121000-min-width-1366-webp@2x.webp 2x">
        <source media="(min-width: 1366px)"
                srcset="/storage/82/conversions/irineu-junior-200810171121000-min-width-1366.jpg, /storage/82/conversions/irineu-junior-200810171121000-min-width-1366@2x.jpg 2x">
        <source media="(min-width: 1280px)"
                srcset="/storage/82/conversions/irineu-junior-200810171121000-min-width-1280-webp.webp, /storage/82/conversions/irineu-junior-200810171121000-min-width-1280-webp@2x.webp 2x">
        <source media="(min-width: 1280px)"
                srcset="/storage/82/conversions/irineu-junior-200810171121000-min-width-1280.jpg, /storage/82/conversions/irineu-junior-200810171121000-min-width-1280@2x.jpg 2x">
        <img src="data:image/png;base64, R0lGODlhAQABAIAAAMLCwgAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw=="
             loading="lazy"
             class="img-fluid"
             title="irineu-junior-200810171121000.jpg"
             alt="irineu-junior-200810171121000.jpg"></picture></a>

<a href="/storage/83/irineu-junior-200810171123000.jpg"
   data-fancybox="images"
   data-caption="irineu-junior-200810171123000.jpg"
   class="">
    <picture>
        <source media="(min-width: 1366px)"
                srcset="/storage/83/conversions/irineu-junior-200810171123000-min-width-1366-webp.webp, /storage/83/conversions/irineu-junior-200810171123000-min-width-1366-webp@2x.webp 2x">
        <source media="(min-width: 1366px)"
                srcset="/storage/83/conversions/irineu-junior-200810171123000-min-width-1366.jpg, /storage/83/conversions/irineu-junior-200810171123000-min-width-1366@2x.jpg 2x">
        <source media="(min-width: 1280px)"
                srcset="/storage/83/conversions/irineu-junior-200810171123000-min-width-1280-webp.webp, /storage/83/conversions/irineu-junior-200810171123000-min-width-1280-webp@2x.webp 2x">
        <source media="(min-width: 1280px)"
                srcset="/storage/83/conversions/irineu-junior-200810171123000-min-width-1280.jpg, /storage/83/conversions/irineu-junior-200810171123000-min-width-1280@2x.jpg 2x">
        <img src="data:image/png;base64, R0lGODlhAQABAIAAAMLCwgAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw=="
             loading="lazy"
             class="img-fluid"
             title="irineu-junior-200810171123000.jpg"
             alt="irineu-junior-200810171123000.jpg"></picture></a>
```

```blade
{{ $model->file() }}
```

```html
/storage/90/irineu-junior-200811140054000.zip
```

## Customizando as views

Pode ser que seja necessário customizar as views que são renderizadas.
Para isso, copie-as (fancy-picture.blade.php / picture.blade.php) para o `packages/frontend/src/resources/views/media`
e as chame no `config/admix-media.php` nos campos:

```php
return [
    ...
    'picture_view' => 'agenciafmd/frontend::media.picture',
    'fancy_picture_view' => 'agenciafmd/media::media.fancy-picture',
];
```

## Licença

Nossos pacotes são abertos, [MIT](https://opensource.org/licenses/MIT) para os mais chegados.

Fique a vontade para começar a montar sua aplicação, mas não se esqueça, a responsabilidade pelo sucesso dela não é nossa 😊 .
