<div>
    <x-slot name="header">
        <div class="flex items-center">
            <h2 class="font-semibold text-xl text-gray-600 leading-tight">
                Tarea Gorda
            </h2>
            <x-button-link class="ml-auto" href="{{route('admin.products.create')}}">
                Agregar producto
            </x-button-link>
        </div>
    </x-slot>
    <x-table-responsive>

        <div class="px-6 py-4">
            <x-jet-input class="w-1/3"
                         wire:model="search"
                         type="text"
                         placeholder="Introduzca el nombre del producto a buscar" />

            <select wire:model="pagination" class="rounded-lg">
                <option value="" selected disabled>Productos a mostrar</option>
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>

            <div x-data="{dropdownMenu: false}" class="relative inline-block">
                <!-- Dropdown toggle button -->
                <x-button-link color="yellow" @click="dropdownMenu = ! dropdownMenu" class="ml-2 flex items-center p-2 bg-white bg-gray-100 rounded-md">
                    <i class="fa-solid fa-table-columns"></i>
                    <span class="ml-4">Mostrar Columnas </span>
                </x-button-link>
                <!-- Dropdown list -->
                <div x-show="dropdownMenu" class="absolute left-0 py-2 mt-2 bg-white bg-gray-100 rounded-md shadow-xl">
                    <spam href="#" class="block px-4 py-2 text-sm">
                        @foreach($columns as $column)
                            <input type="checkbox" wire:model="selectedColumns" value="{{$column}}">
                            <label>{{$column}}</label>
                            <br/>
                        @endforeach
                    </spam>
                </div>
            </div>
        </div>

        @if($products->count())
            <table class="min-w-full divide-y divide-gray-200 overflow-x-auto">
                <thead class="bg-gray-50">
                <tr>
                    @if($this->showColumn('Nombre'))
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nombre
                        </th>
                    @endif
                    @if($this->showColumn('Categoría'))
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Categoría
                        </th>
                    @endif
                    @if($this->showColumn('Estado'))
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                    @endif
                    @if($this->showColumn('Precio'))
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Precio
                        </th>
                    @endif
                    @if($this->showColumn('Subcategoria'))
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Subcategoria
                        </th>
                    @endif
                    @if($this->showColumn('Marca'))
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Marca
                        </th>
                    @endif
                    @if($this->showColumn('Stock'))
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Stock
                        </th>
                    @endif
                    @if($this->showColumn('Colores'))
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Colores
                        </th>
                    @endif
                    @if($this->showColumn('Tallas'))
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tallas
                        </th>
                    @endif
                    @if($this->showColumn('Fecha Creación'))
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fecha Creación
                        </th>
                    @endif
                    @if($this->showColumn('Fecha Edición'))
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fecha Edición
                        </th>
                    @endif
                    <th scope="col" class="relative px-6 py-3">
                        <span class="sr-only">Editar</span>
                    </th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach($products as $product)
                    {{ dd($prueba) }}
                    {{--{{dd($product->colors->all())}}--}}
                    <tr>
                        @if($this->showColumn('Nombre'))
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 object-cover">
                                    <img class="h-10 w-10 rounded-full" src="{{ $product->images->count() ? Storage::url($product->images->first()->url) :'img/default.jpg'  }}" alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $product->name }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        @endif
                        @if($this->showColumn('Categoría'))
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $product->subcategory->category->name }}</div>
                            </td>
                        @endif
                        @if($this->showColumn('Estado'))
                        <td class="px-6 py-4 whitespace-nowrap">
                         <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $product->status == 1 ? 'red' : 'green'
                        }}-100 text-{{ $product->status == 1 ? 'red' : 'green' }}-800">
                            {{ $product->status == 1 ? 'Borrador' : 'Publicado' }}
                         </span>
                        </td>
                        @endif
                        @if($this->showColumn('Precio'))
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $product->price }} &euro;
                        </td>
                        @endif
                        @if($this->showColumn('Subcategoria'))
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $product->subcategory->name }}</div>
                        </td>
                        @endif
                        @if($this->showColumn('Marca'))
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $product->brand->name }}</div>
                        </td>
                        @endif
                        @if($this->showColumn('Stock'))
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if(!isset($product->quantity))
                                @if($product->colors->count() == 0)
                                    <div class="text-sm text-gray-500">
                                        {{ array_sum($product->sizes->pluck('colors')->collapse()->pluck('pivot')->pluck('quantity')->all()) }}</div>
                                @else
                                    <div class="text-sm text-gray-500">{{ array_sum($product->colors->pluck('pivot')->pluck('quantity')->all()) }}</div
                                @endif
                            @else
                                <div class="text-sm text-gray-500">{{ $product->quantity }}</div>
                            @endif
                        </td>
                        @endif
                        @if($this->showColumn('Colores'))
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($product->sizes)
                                    <div class="text-sm text-gray-500">{{ implode(', ',array_unique($product->sizes->pluck('colors')->collapse()->pluck('name')->all()))}}</div>
                                @endif
                                @if($product->colors)
                                    <div class="text-sm text-gray-500">{{implode(', ',$product->colors->pluck('name')->all())}}</div>
                                @endif
                            </td>
                        @endif
                        @if($this->showColumn('Tallas'))
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ implode(', ',$product->sizes->pluck('name')->all()) }}</div>
                        </td>
                        @endif
                        @if($this->showColumn('Fecha Creación'))
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $product->created_at }}</div>
                        </td>
                        @endif
                        @if($this->showColumn('Fecha Edición'))
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $product->updated_at }}</div>
                        </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="px-6 py-4">
                No existen productos coincidentes
            </div>
        @endif
        @if($products->hasPages())
            <div class="px-6 py-4">
                {{ $products->links() }}
            </div>
        @endif
    </x-table-responsive>
</div>