@extends('layouts.app')

@section('content')
<product-import inline-template>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h2>Termékek importálása</h2>
                    </div>
                    <div class="panel-body">
                        <p>Termékek importálása  ({{ $url }}).</p>
                        <button class="btn btn-primary" @click="click" :disabled="inProgress" v-text="inProgress ? 'Folyamatban' : 'Importálás'"></button>

                        <div class="stat-container" v-if="!inProgress">
                            <table class="table" v-if="stat.all">
                                <thead>
                                <tr>
                                    <th scope="col">Összes termék</th>
                                    <th scope="col">Sikeresen létrehozva</th>
                                    <th scope="col">Létrehozva hibával</th>
                                    <th scope="col">Sikertelen</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <th scope="row" v-text="stat.all"></th>
                                    <td v-text="stat.passed"></td>
                                    <td v-text="stat.createdWithError"></td>
                                    <td v-text="stat.failed"></td>
                                </tr>
                                </tbody>
                            </table>
                            <h4 v-if="stat.failedProducts && stat.failedProducts.length > 0">Hibák</h4>
                            <ul class="list-group">
                                <li v-for="item in stat.failedProducts" class="list-group-item">
                                <span v-if="item.product.id">
                                    <span v-text="failedProductText(item)"></span>
                                    <a :href="productUrl(item.product)" target="_blank">Termék adatlap</a>
                                </span>
                                <span v-else v-text="failedProductText(item)"></span>
                                </li>
                            </ul>

                            <h4 v-if="stat.passedProducts && stat.passedProducts.length > 0" style="margin-top:20px">Sikeresen importált termékek</h4>
                            <ul class="list-group">
                                <li v-for="product in stat.passedProducts" class="list-group-item">
                                    <a :href="productUrl(product)" v-text="product.name" target="_blank"></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</product-import>
@endsection
