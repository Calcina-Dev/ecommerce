"use client";
import React, { useEffect, useRef, useState } from "react";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import { MapPin, Navigation, Loader2 } from "lucide-react";

interface AddressMapSelectorProps {
  onSelectLocation: (data: {
    address: string;
    district: string;
    province: string;
    department: string;
    lat: number;
    lng: number;
  }) => void;
  initialLat?: number;
  initialLng?: number;
}

export function AddressMapSelector({
  onSelectLocation,
  initialLat = -12.0464, // Lima por defecto
  initialLng = -77.0428,
}: AddressMapSelectorProps) {
  const mapContainerRef = useRef<HTMLDivElement>(null);
  const mapInstanceRef = useRef<L.Map | null>(null);
  const markerRef = useRef<L.Marker | null>(null);

  const [loadingGeo, setLoadingGeo] = useState(false);
  const [selectedText, setSelectedText] = useState<string>("Mueve el pin o haz clic en el mapa para seleccionar tu dirección exacta");

  const reverseGeocode = async (lat: number, lng: number) => {
    setLoadingGeo(true);
    try {
      const res = await fetch(
        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
      );
      if (res.ok) {
        const data = await res.json();
        const addr = data.address || {};
        
        const road = addr.road || addr.pedestrian || addr.suburb || addr.neighbourhood || "";
        const houseNumber = addr.house_number || "";
        const fullStreet = `${road} ${houseNumber}`.trim() || data.display_name?.split(",")[0] || "Dirección en mapa";
        
        const district = addr.suburb || addr.city_district || addr.town || addr.city || "Lima";
        const province = addr.state_district || addr.county || addr.city || "Lima";
        const department = addr.state || "Lima";

        setSelectedText(`${fullStreet}, ${district}`);
        onSelectLocation({
          address: fullStreet,
          district: district,
          province: province,
          department: department,
          lat,
          lng,
        });
      }
    } catch (err) {
      console.error("Error en geocodificación:", err);
      setSelectedText(`Coordenadas: ${lat.toFixed(4)}, ${lng.toFixed(4)}`);
    } finally {
      setLoadingGeo(false);
    }
  };

  useEffect(() => {
    if (!mapContainerRef.current) return;
    if (mapInstanceRef.current) return; // Ya inicializado

    // Crear mapa
    const map = L.map(mapContainerRef.current, {
      center: [initialLat, initialLng],
      zoom: 14,
      zoomControl: true,
    });
    mapInstanceRef.current = map;

    // Capa OpenStreetMap
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      maxZoom: 19,
    }).addTo(map);

    // Icono personalizado de pin para evitar errores de Webpack con PNGs de Leaflet
    const customPinIcon = L.divIcon({
      className: "custom-map-marker",
      html: `<div style="font-size: 32px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.4)); transform: translate(-50%, -100%); cursor: grab;">📍</div>`,
      iconSize: [32, 32],
      iconAnchor: [16, 32],
    });

    // Crear marcador arrastrable
    const marker = L.marker([initialLat, initialLng], {
      icon: customPinIcon,
      draggable: true,
    }).addTo(map);
    markerRef.current = marker;

    // Al arrastrar el marcador
    marker.on("dragend", () => {
      const pos = marker.getLatLng();
      reverseGeocode(pos.lat, pos.lng);
    });

    // Al hacer clic en cualquier parte del mapa
    map.on("click", (e: L.LeafletMouseEvent) => {
      marker.setLatLng(e.latlng);
      map.panTo(e.latlng);
      reverseGeocode(e.latlng.lat, e.latlng.lng);
    });

    // Geocodificación inicial
    reverseGeocode(initialLat, initialLng);

    return () => {
      map.remove();
      mapInstanceRef.current = null;
    };
  }, []);

  const handleUseGPS = () => {
    if (!navigator.geolocation) {
      alert("Tu navegador no soporta geolocalización");
      return;
    }
    setLoadingGeo(true);
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        const { latitude, longitude } = pos.coords;
        if (mapInstanceRef.current && markerRef.current) {
          const newLatLng = new L.LatLng(latitude, longitude);
          markerRef.current.setLatLng(newLatLng);
          mapInstanceRef.current.setView(newLatLng, 16);
          reverseGeocode(latitude, longitude);
        }
      },
      (err) => {
        setLoadingGeo(false);
        alert("No se pudo obtener tu ubicación GPS. Verifica los permisos de tu navegador.");
      },
      { enableHighAccuracy: true }
    );
  };

  const handleQuickCity = (lat: number, lng: number, name: string) => {
    if (mapInstanceRef.current && markerRef.current) {
      const newLatLng = new L.LatLng(lat, lng);
      markerRef.current.setLatLng(newLatLng);
      mapInstanceRef.current.setView(newLatLng, 15);
      reverseGeocode(lat, lng);
    }
  };

  return (
    <div className="space-y-2.5 my-3 bg-muted/30 p-3 rounded-2xl border border-border">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <span className="text-xs font-bold text-foreground flex items-center gap-1.5">
          <MapPin className="w-4 h-4 text-primary" />
          Selecciona tu ubicación en el mapa:
        </span>
        <button
          type="button"
          onClick={handleUseGPS}
          className="text-xs bg-primary text-primary-foreground px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-sm hover:opacity-90 transition-opacity"
        >
          <Navigation className="w-3.5 h-3.5" />
          Usar mi GPS actual
        </button>
      </div>

      {/* Botones rápidos de ciudades/zonas populares */}
      <div className="flex items-center gap-1.5 overflow-x-auto pb-1 text-[11px]">
        <span className="text-muted-foreground font-medium shrink-0">Zonas rápidas:</span>
        <button
          type="button"
          onClick={() => handleQuickCity(-7.2244, -79.4328, "Chepén")}
          className="px-2.5 py-1 bg-background hover:bg-muted border rounded-lg font-semibold transition-colors shrink-0"
        >
          Chepén
        </button>
        <button
          type="button"
          onClick={() => handleQuickCity(-8.1159, -79.0299, "Trujillo")}
          className="px-2.5 py-1 bg-background hover:bg-muted border rounded-lg font-semibold transition-colors shrink-0"
        >
          Trujillo
        </button>
        <button
          type="button"
          onClick={() => handleQuickCity(-12.0464, -77.0428, "Lima Centro")}
          className="px-2.5 py-1 bg-background hover:bg-muted border rounded-lg font-semibold transition-colors shrink-0"
        >
          Lima Centro
        </button>
        <button
          type="button"
          onClick={() => handleQuickCity(-6.7714, -79.8409, "Chiclayo")}
          className="px-2.5 py-1 bg-background hover:bg-muted border rounded-lg font-semibold transition-colors shrink-0"
        >
          Chiclayo
        </button>
        <button
          type="button"
          onClick={() => handleQuickCity(-16.409, -71.5375, "Arequipa")}
          className="px-2.5 py-1 bg-background hover:bg-muted border rounded-lg font-semibold transition-colors shrink-0"
        >
          Arequipa
        </button>
      </div>

      {/* Contenedor del Mapa */}
      <div
        ref={mapContainerRef}
        className="w-full h-52 sm:h-60 rounded-xl overflow-hidden border-2 border-primary/20 shadow-inner z-10 relative bg-zinc-100 dark:bg-zinc-800"
      />

      {/* Barra de estado / Dirección detectada */}
      <div className="flex items-center gap-2 bg-background p-2.5 rounded-xl border text-xs">
        {loadingGeo ? (
          <Loader2 className="w-4 h-4 text-primary animate-spin shrink-0" />
        ) : (
          <span className="text-emerald-600 dark:text-emerald-400 font-bold shrink-0">📍 Detectado:</span>
        )}
        <span className="font-medium text-foreground truncate flex-1">
          {loadingGeo ? "Buscando nombre de la calle y distrito..." : selectedText}
        </span>
      </div>
    </div>
  );
}
