@extends('layout.layout')
@php
    $title = $container->title . (is_null($item->id) ? ' Ekle' : ' Düzenle');
    $subTitle = $container->title . (is_null($item->id) ? ' Ekle' : ' Düzenle');
@endphp

@section('content')
    <div class="row gy-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0 fs-6">{{ $container->title }} {{ !is_null($item->id) ? 'Düzenle' : 'Ekle' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.' . $container->page . '_save', ['unique' => $item->id]) }}"
                        method="POST">
                        @csrf
                        <div class="row gy-3">
                            <div class="col-6">
                                <label class="form-label">Şube Adı</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="icon-park-outline:branch-one"></iconify-icon>
                                    </span>
                                    <input type="text" class="form-control" name="title"
                                        placeholder="Lütfen şube adı giriniz"
                                        value="{{ old('title') ?? ($item->title ?? '') }}">
                                    <x-form-error field="title" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Telefonu</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:location-company"></iconify-icon>
                                    </span>
                                    <input type="text" class="form-control" name="phone_number" id="phone"
                                        placeholder="Lütfen şube telefonu giriniz"
                                        value="{{ old('phone') ?? ($item->phone ?? '') }}">
                                    <x-form-error field="phone" />
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Adresi</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:location-company"></iconify-icon>
                                    </span>
                                    <textarea class="form-control" name="address" placeholder="Lütfen şube adresi giriniz"
                                        value="{{ old('address') ?? ($item->address ?? '') }}"></textarea>
                                    <x-form-error field="address" />
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Durumu</label>
                                <div class="icon-field">

                                    <select class="form-select select2" name="is_active">
                                        <option value="1" {{ old('is_active') ?? ($item->is_active ?? '') == 1 ? 'selected' : '' }}>Aktif</option>
                                        <option value="0" {{ old('is_active') ?? ($item->is_active ?? '') == 0 ? 'selected' : '' }}>Pasif</option>
                                    </select>
                                    <x-form-error field="is_active" />
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Alan</label>
                                <div class="icon-field">
                                    <input type="hidden" name="positions">
                                    <div id="map" style="width: 100%; aspect-ratio: 16 / 9; height: 50vh !important;"></div>
                                </div>
                                <x-form-error field="positions" />
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary-600">Kaydet</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
@endsection
@section('script')
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
    <script>
        $(document).ready(function() {
            var maptype_def = L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            });

            var map = new L.Map('map', {
                center: new L.LatLng(37.0588348, 37.3451174),
                zoom: parseInt(15)
            });
            var drawnItems = L.featureGroup().addTo(map);

            // Kaydedilmiş zone.positions değerini kontrol et ve haritaya ekle
            @if(isset($item->zone) && $item->zone->positions)
                try {
                    var savedPolygon = {!! json_encode($item->zone->positions->jsonSerialize()) !!};
                    var geoJsonLayer = L.geoJSON(savedPolygon, {
                        style: {
                            color: '#3388ff',
                            weight: 3,
                            opacity: 0.65
                        }
                    }).addTo(drawnItems);

                    // Mevcut konuma odaklan
                    if(savedPolygon.coordinates && savedPolygon.coordinates[0] && savedPolygon.coordinates[0].length > 0) {
                        var bounds = geoJsonLayer.getBounds();
                        map.fitBounds(bounds);
                    }
                } catch(e) {
                    console.error("Kaydedilmiş bölge yüklenirken hata oluştu:", e);
                }
            @endif

            L.control.layers({
                "Google Hybrid": maptype_def.addTo(map),
                "Google Street": L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                }),
                "Google Satellite": L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                }),
                "Google Terrain": L.tileLayer('http://{s}.google.com/vt/lyrs=p&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                }),
                "OSM": L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 18,
                    attribution: null
                }),
            }, {
                'Draw Layer': drawnItems
            }, {
                position: 'topleft',
                collapsed: true
            }).addTo(map);

            map.addControl(new L.Control.Draw({
                position: 'topright',
                edit: {
                    featureGroup: drawnItems,
                    poly: {
                        allowIntersection: true
                    }
                },
                draw: {
                    marker: false,
                    polyline: false,
                    circlemarker: false,
                    circle: false,
                }
            }));

            // Haritaya çizilen öğeleri temizle, sadece yeni çizilen öğeyi tutalım
            map.on('draw:created', function(event) {
                drawnItems.clearLayers(); // Önceki çizimleri temizle
                var layer = event.layer;
                drawnItems.addLayer(layer);
            });

            $(document).on("submit", function(e) {
                const cords = drawnItems.toGeoJSON()


                if (cords.features.length <= 0 || cords.features.length >= 2) {
                    Swal.fire({
                        title: 'Hata!',
                        text: 'Lütfen 1 adet alan çiziniz!',
                        icon: 'error',
                        confirmButtonText: 'Tamam'
                    })

                    drawnItems.clearLayers()
                    e.preventDefault()
                    return false
                }

                try {
                    $("input[name='positions']").val(JSON.stringify(cords.features[0].geometry))
                    console.log(JSON.stringify(cords.features[0].geometry));
                    return true
                } catch (error) {
                    console.log(error)
                    e.preventDefault()
                    return false
                }

            });
        });
    </script>
@endsection
