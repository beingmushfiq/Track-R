import { useState } from 'react';
import { Lock, Unlock, AlertTriangle, Clock } from 'lucide-react';
import api from '../services/api';

export default function EngineControlPanel({ device, onUpdate }) {
  const [loading, setLoading] = useState(false);
  const [showConfirm, setShowConfirm] = useState(null);

  const handleEngineControl = async (action) => {
    setLoading(true);
    try {
      const endpoint = action === 'lock' ? `/api/devices/${device.id}/lock` : `/api/devices/${device.id}/unlock`;
      const response = await api.post(endpoint);
      
      if (onUpdate) {
        onUpdate(response.data);
      }
      
      setShowConfirm(null);
    } catch (error) {
      console.error('Engine control error:', error);
      alert(error.response?.data?.message || 'Failed to control engine');
    } finally {
      setLoading(false);
    }
  };

  const ConfirmDialog = ({ action }) => (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div className="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div className="flex items-center gap-3 mb-4">
          <AlertTriangle className="text-orange-500" size={24} />
          <h3 className="text-lg font-semibold">
            Confirm Engine {action === 'lock' ? 'Lock' : 'Unlock'}
          </h3>
        </div>
        
        <p className="text-gray-600 mb-6">
          Are you sure you want to {action === 'lock' ? 'lock' : 'unlock'} the engine for{' '}
          <span className="font-semibold">{device.vehicle?.name || device.imei}</span>?
        </p>
        
        <div className="flex gap-3 justify-end">
          <button
            onClick={() => setShowConfirm(null)}
            className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
            disabled={loading}
          >
            Cancel
          </button>
          <button
            onClick={() => handleEngineControl(action)}
            className={`px-4 py-2 rounded-lg text-white ${
              action === 'lock'
                ? 'bg-red-600 hover:bg-red-700'
                : 'bg-green-600 hover:bg-green-700'
            }`}
            disabled={loading}
          >
            {loading ? 'Processing...' : `Yes, ${action === 'lock' ? 'Lock' : 'Unlock'}`}
          </button>
        </div>
      </div>
    </div>
  );

  return (
    <div className="bg-white rounded-lg shadow p-4">
      <h3 className="text-lg font-semibold mb-4">Engine Control</h3>
      
      {/* Current Status */}
      <div className="mb-4 p-3 bg-gray-50 rounded-lg">
        <div className="flex items-center justify-between">
          <span className="text-sm text-gray-600">Current Status:</span>
          <span className={`flex items-center gap-2 font-semibold ${
            device.engine_locked ? 'text-red-600' : 'text-green-600'
          }`}>
            {device.engine_locked ? (
              <>
                <Lock size={16} />
                Locked
              </>
            ) : (
              <>
                <Unlock size={16} />
                Unlocked
              </>
            )}
          </span>
        </div>
        
        {device.last_command_at && (
          <div className="flex items-center gap-2 text-xs text-gray-500 mt-2">
            <Clock size={12} />
            Last command: {new Date(device.last_command_at).toLocaleString()}
          </div>
        )}
      </div>

      {/* Control Buttons */}
      <div className="grid grid-cols-2 gap-3">
        <button
          onClick={() => setShowConfirm('lock')}
          disabled={loading || device.engine_locked}
          className={`flex items-center justify-center gap-2 px-4 py-3 rounded-lg font-medium transition-colors ${
            device.engine_locked
              ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
              : 'bg-red-600 text-white hover:bg-red-700'
          }`}
        >
          <Lock size={18} />
          Lock Engine
        </button>
        
        <button
          onClick={() => setShowConfirm('unlock')}
          disabled={loading || !device.engine_locked}
          className={`flex items-center justify-center gap-2 px-4 py-3 rounded-lg font-medium transition-colors ${
            !device.engine_locked
              ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
              : 'bg-green-600 text-white hover:bg-green-700'
          }`}
        >
          <Unlock size={18} />
          Unlock Engine
        </button>
      </div>

      {/* Safety Warning */}
      <div className="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
        <p className="text-xs text-yellow-800">
          <strong>Safety Notice:</strong> Ensure the vehicle is safely parked before locking the engine.
          Unlocking should only be done by authorized personnel.
        </p>
      </div>

      {showConfirm && <ConfirmDialog action={showConfirm} />}
    </div>
  );
}
