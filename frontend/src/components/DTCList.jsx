import React, { useState } from 'react';
import { AlertTriangle, CheckCircle, Trash2 } from 'lucide-react';
import api from '../services/api';

const DTCList = ({ codes, onClear }) => {
  const [clearing, setClearing] = useState(null);

  const handleClear = async (codeId) => {
    setClearing(codeId);
    try {
      await api.post(`/diagnostics/codes/${codeId}/clear`, {
        notes: 'Cleared via dashboard'
      });
      if (onClear) onClear();
    } catch (error) {
      console.error('Failed to clear code:', error);
    } finally {
      setClearing(null);
    }
  };

  if (!codes || codes.length === 0) {
    return (
      <div className="flex flex-col items-center justify-center p-8 bg-gray-50 rounded-xl border border-dashed border-gray-200">
        <CheckCircle className="w-8 h-8 text-emerald-500 mb-2" />
        <h3 className="text-sm font-medium text-gray-900">No active diagnostic codes</h3>
        <p className="text-xs text-gray-500">System is running normally</p>
      </div>
    );
  }

  const getSeverityColor = (severity) => {
    switch (severity) {
      case 'critical': return 'bg-red-50 text-red-700 border-red-100';
      case 'high': return 'bg-orange-50 text-orange-700 border-orange-100';
      case 'medium': return 'bg-amber-50 text-amber-700 border-amber-100';
      case 'low': return 'bg-blue-50 text-blue-700 border-blue-100';
      default: return 'bg-gray-50 text-gray-700 border-gray-100';
    }
  };

  return (
    <div className="space-y-3">
      {codes.map((code) => (
        <div key={code.id} className={`p-4 rounded-lg border ${getSeverityColor(code.severity)} flex items-start justify-between`}>
          <div className="flex gap-3">
            <AlertTriangle className="w-5 h-5 shrink-0 mt-0.5" />
            <div>
              <div className="flex items-center gap-2">
                <span className="font-bold">{code.code}</span>
                <span className="text-xs px-2 py-0.5 bg-white/50 rounded-full font-medium uppercase">{code.severity}</span>
              </div>
              <p className="text-sm mt-1 opacity-90">{code.description}</p>
              <p className="text-xs mt-2 opacity-75">
                Detected: {new Date(code.detected_at).toLocaleString()}
              </p>
            </div>
          </div>
          <button
            onClick={() => handleClear(code.id)}
            disabled={clearing === code.id}
            className="p-2 hover:bg-white/50 rounded-lg transition-colors"
            title="Clear Code"
          >
            <Trash2 size={16} />
          </button>
        </div>
      ))}
    </div>
  );
};

export default DTCList;
