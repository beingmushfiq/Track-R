import { useState } from 'react';
import { X, Car, MapPin, Clock, Battery, Gauge } from 'lucide-react';
import EngineControlPanel from './EngineControlPanel';

export default function VehicleDetailsModal({ vehicle, onClose, onUpdate }) {
  if (!vehicle) return null;

  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        {/* Header */}
        <div className="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center">
              <Car className="w-6 h-6 text-indigo-600" />
            </div>
            <div>
              <h2 className="text-xl font-semibold">{vehicle.name}</h2>
              <p className="text-sm text-gray-600">{vehicle.type}</p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
          >
            <X size={20} />
          </button>
        </div>

        {/* Content */}
        <div className="p-6 space-y-6">
          {/* Vehicle Info Grid */}
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div className="bg-gray-50 rounded-lg p-4">
              <div className="flex items-center gap-2 text-gray-600 mb-1">
                <MapPin size={16} />
                <span className="text-sm">Location</span>
              </div>
              <p className="font-semibold text-gray-900">{vehicle.location}</p>
            </div>

            <div className="bg-gray-50 rounded-lg p-4">
              <div className="flex items-center gap-2 text-gray-600 mb-1">
                <Gauge size={16} />
                <span className="text-sm">Speed</span>
              </div>
              <p className="font-semibold text-gray-900">{vehicle.speed} km/h</p>
            </div>

            <div className="bg-gray-50 rounded-lg p-4">
              <div className="flex items-center gap-2 text-gray-600 mb-1">
                <Battery size={16} />
                <span className="text-sm">Battery</span>
              </div>
              <p className="font-semibold text-gray-900">{vehicle.battery}%</p>
            </div>

            <div className="bg-gray-50 rounded-lg p-4">
              <div className="flex items-center gap-2 text-gray-600 mb-1">
                <Clock size={16} />
                <span className="text-sm">Last Update</span>
              </div>
              <p className="font-semibold text-gray-900">{vehicle.lastUpdate}</p>
            </div>
          </div>

          {/* Engine Control */}
          {vehicle.device && (
            <EngineControlPanel 
              device={vehicle.device} 
              onUpdate={(updatedDevice) => {
                if (onUpdate) {
                  onUpdate({ ...vehicle, device: updatedDevice });
                }
              }}
            />
          )}

          {/* Additional Details */}
          <div className="bg-gray-50 rounded-lg p-4">
            <h3 className="font-semibold mb-3">Vehicle Details</h3>
            <div className="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span className="text-gray-600">Driver:</span>
                <span className="ml-2 font-medium">{vehicle.driver}</span>
              </div>
              <div>
                <span className="text-gray-600">Status:</span>
                <span className="ml-2 font-medium capitalize">{vehicle.status}</span>
              </div>
              {vehicle.registration && (
                <div>
                  <span className="text-gray-600">Registration:</span>
                  <span className="ml-2 font-medium">{vehicle.registration}</span>
                </div>
              )}
              {vehicle.device?.imei && (
                <div>
                  <span className="text-gray-600">Device IMEI:</span>
                  <span className="ml-2 font-medium font-mono text-xs">{vehicle.device.imei}</span>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Footer */}
        <div className="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-end gap-3">
          <button
            onClick={onClose}
            className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-white transition-colors"
          >
            Close
          </button>
          <button className="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
            View on Map
          </button>
        </div>
      </div>
    </div>
  );
}
