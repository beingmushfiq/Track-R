import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Switch } from "@/components/ui/switch";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Loader2, Mail, Clock } from "lucide-react";
import api from '../services/api';
import { useToast } from "@/components/ui/use-toast";

const ReportSubscriptionManager = () => {
  const [loading, setLoading] = useState(true);
  const [subscription, setSubscription] = useState(null);
  const [processing, setProcessing] = useState(false);
  const { toast } = useToast();

  useEffect(() => {
    fetchSubscription();
  }, []);

  const fetchSubscription = async () => {
    try {
      const response = await api.get('/reports/subscriptions');
      // For now, assume single 'daily' subscription logic for simplicity
      // Or filter from list
      const dailySub = response.data.find(sub => sub.report_type === 'daily');
      setSubscription(dailySub || null);
    } catch (error) {
      console.error("Failed to fetch subscriptions:", error);
    } finally {
      setLoading(false);
    }
  };

  const handleToggle = async (checked) => {
    setProcessing(true);
    try {
      if (checked) {
        // Create subscription
        const response = await api.post('/reports/subscriptions', {
          report_type: 'daily',
          delivery_method: 'email', // Default
          delivery_time: '08:00', // Default
        });
        setSubscription(response.data);
        toast({ title: "Subscribed", description: "You will receive daily summary reports." });
      } else {
        // Delete subscription
        if (subscription) {
          await api.delete(`/reports/subscriptions/${subscription.id}`);
          setSubscription(null);
          toast({ title: "Unsubscribed", description: "Daily reports disabled." });
        }
      }
    } catch (error) {
      console.error("Subscription update failed:", error);
      toast({ title: "Error", description: "Failed to update subscription.", variant: "destructive" });
    } finally {
      setProcessing(false);
    }
  };

  if (loading) return <div className="h-20 flex items-center justify-center"><Loader2 className="animate-spin" /></div>;

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Mail className="h-5 w-5" />
          Email Subscriptions
        </CardTitle>
        <CardDescription>
          Manage your automated daily report settings.
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-4">
        <div className="flex items-center justify-between space-x-2">
          <div className="space-y-0.5">
            <Label htmlFor="daily-emails">Daily Activity Summary</Label>
            <p className="text-sm text-muted-foreground">
              Receive a summary of yesterday's fleet activity every morning.
            </p>
          </div>
          <Switch
            id="daily-emails"
            checked={!!subscription && subscription.is_active}
            onCheckedChange={handleToggle}
            disabled={processing}
          />
        </div>

        {subscription && (
          <div className="flex items-center gap-4 pt-2">
             <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <Clock className="h-4 w-4" />
                Delivery Time: <span className="font-medium text-foreground">{subscription.delivery_time || "08:00"}</span>
             </div>
             {/* Add time picker here later if needed */}
          </div>
        )}
      </CardContent>
    </Card>
  );
};

export default ReportSubscriptionManager;
