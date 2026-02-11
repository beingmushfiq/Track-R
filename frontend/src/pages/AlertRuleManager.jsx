import { useState } from 'react';
import { Plus, Edit, Trash2, Bell, AlertTriangle } from 'lucide-react';
import { Card, Button, Modal, Input, Select, Badge } from '../components/ui';

const AlertRuleManager = () => {
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);

  // Mock alert rules
  const alertRules = [
    {
      id: 1,
      name: 'Speed Limit Violation',
      type: 'speed',
      condition: 'greater_than',
      value: 80,
      unit: 'km/h',
      severity: 'high',
      active: true,
      triggeredCount: 45,
    },
    {
      id: 2,
      name: 'Geofence Exit Alert',
      type: 'geofence',
      condition: 'exit',
      geofence: 'Warehouse Zone',
      severity: 'medium',
      active: true,
      triggeredCount: 12,
    },
    {
      id: 3,
      name: 'Idle Time Warning',
      type: 'idle',
      condition: 'greater_than',
      value: 30,
      unit: 'minutes',
      severity: 'low',
      active: false,
      triggeredCount: 8,
    },
  ];

  const alertTypes = [
    { value: 'speed', label: 'Speed' },
    { value: 'geofence', label: 'Geofence' },
    { value: 'idle', label: 'Idle Time' },
    { value: 'maintenance', label: 'Maintenance' },
    { value: 'fuel', label: 'Fuel Level' },
  ];

  const conditionOptions = [
    { value: 'greater_than', label: 'Greater Than' },
    { value: 'less_than', label: 'Less Than' },
    { value: 'equals', label: 'Equals' },
    { value: 'enter', label: 'Enter' },
    { value: 'exit', label: 'Exit' },
  ];

  const severityOptions = [
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' },
    { value: 'critical', label: 'Critical' },
  ];

  const getSeverityBadge = (severity) => {
    const variants = {
      low: 'default',
      medium: 'warning',
      high: 'danger',
      critical: 'danger',
    };
    return <Badge variant={variants[severity]}>{severity.toUpperCase()}</Badge>;
  };

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-2xl font-display font-bold text-slate-900 dark:text-white">Alert Rules</h1>
          <p className="text-slate-500 dark:text-slate-400">Configure custom alert rules for your fleet</p>
        </div>
        <Button onClick={() => setIsAddModalOpen(true)}>
          <Plus className="w-4 h-4 mr-2" />
          Create Rule
        </Button>
      </div>

      {/* Alert Rules Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {alertRules.map((rule) => (
          <Card key={rule.id} hover>
            <Card.Body>
              <div className="flex items-start justify-between mb-4">
                <div className="flex items-center gap-3">
                  <div className={`w-12 h-12 rounded-xl flex items-center justify-center ${
                    rule.active 
                      ? 'bg-indigo-50 dark:bg-indigo-500/10' 
                      : 'bg-slate-100 dark:bg-slate-800'
                  }`}>
                    <Bell className={`w-6 h-6 ${
                      rule.active 
                        ? 'text-indigo-600 dark:text-indigo-400' 
                        : 'text-slate-400'
                    }`} />
                  </div>
                  <div>
                    <h3 className="font-semibold text-slate-900 dark:text-white">{rule.name}</h3>
                    <p className="text-sm text-slate-500 dark:text-slate-400 capitalize">{rule.type}</p>
                  </div>
                </div>
                <div className="flex gap-1">
                  <button className="p-1 hover:bg-slate-100 dark:hover:bg-slate-800 rounded">
                    <Edit className="w-4 h-4 text-slate-500" />
                  </button>
                  <button className="p-1 hover:bg-slate-100 dark:hover:bg-slate-800 rounded">
                    <Trash2 className="w-4 h-4 text-red-500" />
                  </button>
                </div>
              </div>

              <div className="space-y-3">
                <div className="flex items-center justify-between">
                  <span className="text-sm text-slate-600 dark:text-slate-400">Severity</span>
                  {getSeverityBadge(rule.severity)}
                </div>

                <div className="flex items-center justify-between">
                  <span className="text-sm text-slate-600 dark:text-slate-400">Status</span>
                  <Badge variant={rule.active ? 'success' : 'default'}>
                    {rule.active ? 'Active' : 'Inactive'}
                  </Badge>
                </div>

                <div className="flex items-center justify-between">
                  <span className="text-sm text-slate-600 dark:text-slate-400">Triggered</span>
                  <span className="font-semibold text-slate-900 dark:text-white">
                    {rule.triggeredCount} times
                  </span>
                </div>

                {rule.value && (
                  <div className="pt-3 border-t border-slate-100 dark:border-slate-800">
                    <p className="text-sm text-slate-600 dark:text-slate-400">
                      Condition: <span className="font-medium text-slate-900 dark:text-white">
                        {rule.condition.replace('_', ' ')} {rule.value} {rule.unit}
                      </span>
                    </p>
                  </div>
                )}

                {rule.geofence && (
                  <div className="pt-3 border-t border-slate-100 dark:border-slate-800">
                    <p className="text-sm text-slate-600 dark:text-slate-400">
                      Geofence: <span className="font-medium text-slate-900 dark:text-white">
                        {rule.geofence}
                      </span>
                    </p>
                  </div>
                )}
              </div>
            </Card.Body>
          </Card>
        ))}
      </div>

      {/* Add Alert Rule Modal */}
      <Modal
        isOpen={isAddModalOpen}
        onClose={() => setIsAddModalOpen(false)}
        title="Create Alert Rule"
        size="md"
      >
        <div className="space-y-4">
          <Input label="Rule Name" placeholder="e.g., Speed Limit Violation" />
          
          <Select
            label="Alert Type"
            options={alertTypes}
            placeholder="Select type"
          />

          <Select
            label="Condition"
            options={conditionOptions}
            placeholder="Select condition"
          />

          <Input 
            label="Threshold Value" 
            type="number" 
            placeholder="e.g., 80"
          />

          <Select
            label="Severity"
            options={severityOptions}
            placeholder="Select severity"
          />

          <div className="flex items-center gap-2">
            <input
              type="checkbox"
              id="active"
              className="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500"
              defaultChecked
            />
            <label htmlFor="active" className="text-sm text-slate-700 dark:text-slate-300">
              Activate rule immediately
            </label>
          </div>

          <div className="p-4 bg-orange-50 dark:bg-orange-500/10 rounded-lg border border-orange-200 dark:border-orange-800">
            <div className="flex gap-2">
              <AlertTriangle className="w-5 h-5 text-orange-600 dark:text-orange-400 flex-shrink-0" />
              <p className="text-sm text-orange-800 dark:text-orange-400">
                Alert rules will be evaluated in real-time for all vehicles in your fleet.
              </p>
            </div>
          </div>
        </div>
        <Modal.Footer>
          <Button variant="secondary" onClick={() => setIsAddModalOpen(false)}>
            Cancel
          </Button>
          <Button onClick={() => setIsAddModalOpen(false)}>
            Create Rule
          </Button>
        </Modal.Footer>
      </Modal>
    </div>
  );
};

export default AlertRuleManager;
