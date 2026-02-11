import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, RefreshCw, Activity, ShieldCheck, AlertCircle } from 'lucide-react';
import api from '../services/api';
import HealthScoreGauge from '../components/HealthScoreGauge';
import DiagnosticMetrics from '../components/DiagnosticMetrics';
import DTCList from '../components/DTCList';
import EngineMetricsChart from '../components/EngineMetricsChart';

const EngineDiagnostics = () => {
  // ... existing code ...

  return (
    <div className="p-6 max-w-7xl mx-auto space-y-6">
      {/* ... existing code ... */}

      {/* Middle Section: Diagnostic Codes */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
          <h2 className="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <AlertTriangle size={20} className="text-amber-500" />
            Diagnostic Trouble Codes (DTC)
          </h2>
          <DTCList 
            codes={data?.codes} 
            onClear={fetchDiagnostics} 
          />
        </div>

        {/* Charts/Trends */}
        <div className="space-y-6">
           <EngineMetricsChart 
             data={data?.trends} 
             metric="avg_rpm" 
             label="Average RPM" 
             color="#4f46e5"
           />
           <EngineMetricsChart 
             data={data?.trends} 
             metric="avg_coolant_temp" 
             label="Coolant Temperature" 
             color="#ef4444"
           />
        </div>
      </div>
    </div>
  );
};

export default EngineDiagnostics;
