import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Calendar } from "@/components/ui/calendar"; // Assuming standard UI component
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { format } from "date-fns";
import { Calendar as CalendarIcon, Download, FileText, Truck, Activity, Fuel, Navigation } from "lucide-react";
import { cn } from "@/lib/utils";
import api from '../services/api';
import SummaryCard from '../components/SummaryCard';
import VehicleActivityTable from '../components/VehicleActivityTable';
import ReportSubscriptionManager from '../components/ReportSubscriptionManager';
import { useToast } from "@/components/ui/use-toast";

const DailySummary = () => {
  const [date, setDate] = useState(new Date());
  const [loading, setLoading] = useState(true);
  const [data, setData] = useState(null);
  const { toast } = useToast();

  useEffect(() => {
    fetchDailySummary(date);
  }, [date]);

  const fetchDailySummary = async (selectedDate) => {
    setLoading(true);
    try {
      const formattedDate = format(selectedDate, 'yyyy-MM-dd');
      const response = await api.get(`/reports/daily-summary?date=${formattedDate}`);
      setData(response.data);
    } catch (error) {
      console.error("Failed to fetch daily summary:", error);
      toast({ title: "Error", description: "Could not load report data.", variant: "destructive" });
    } finally {
      setLoading(false);
    }
  };

  const handleExport = async () => {
    try {
      const formattedDate = format(date, 'yyyy-MM-dd');
      // This would normally trigger a file download
      // For now, just call the API which returns a placeholder 501
      await api.post('/reports/daily-summary/export', { date: formattedDate });
      toast({ title: "Export Started", description: "Your PDF is being generated..." });
    } catch (error) {
       toast({ title: "Export Failed", description: "PDF generation is not yet implemented.", variant: "default" });
    }
  };

  return (
    <div className="p-6 space-y-6">
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Daily Activity Summary</h1>
          <p className="text-muted-foreground">
            Overview of fleet performance for <span className="font-medium text-foreground">{format(date, 'MMMM do, yyyy')}</span>
          </p>
        </div>
        
        <div className="flex items-center gap-2">
          <Popover>
            <PopoverTrigger asChild>
              <Button variant="outline" className={cn("w-[240px] justify-start text-left font-normal", !date && "text-muted-foreground")}>
                <CalendarIcon className="mr-2 h-4 w-4" />
                {date ? format(date, "PPP") : <span>Pick a date</span>}
              </Button>
            </PopoverTrigger>
            <PopoverContent className="w-auto p-0" align="end">
              <Calendar
                mode="single"
                selected={date}
                onSelect={(d) => d && setDate(d)}
                initialFocus
              />
            </PopoverContent>
          </Popover>

          <Button variant="outline" onClick={handleExport}>
            <Download className="mr-2 h-4 w-4" />
            Export PDF
          </Button>
        </div>
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <SummaryCard
          title="Total Distance"
          value={data?.summary?.total_distance_km ? `${data.summary.total_distance_km} km` : "0 km"}
          icon={<Navigation className="h-4 w-4" />}
          subtitle="Traveled today"
        />
        <SummaryCard
          title="Fuel Consumed"
          value={data?.summary?.total_fuel_liters ? `${data.summary.total_fuel_liters} L` : "0 L"}
          icon={<Fuel className="h-4 w-4" />}
          subtitle="Estimated usage"
        />
        <SummaryCard
          title="Active Vehicles"
          value={data?.summary?.active_vehicles || 0}
          icon={<Truck className="h-4 w-4" />}
          subtitle={`Out of ${data?.summary?.total_vehicles || 0} total`}
        />
        <SummaryCard
          title="Safety Events"
          value={data?.summary?.safety_events || 0}
          icon={<Activity className="h-4 w-4" />}
          trend={data?.summary?.safety_events > 0 ? "+Alerts" : null}
          className={data?.summary?.safety_events > 0 ? "border-red-200 bg-red-50 dark:bg-red-900/10" : ""}
        />
      </div>

      <div className="grid gap-4 md:grid-cols-7">
        <Card className="col-span-1 md:col-span-5">
           <CardHeader>
             <CardTitle>Vehicle Activity Breakdown</CardTitle>
             <CardDescription>Detailed stats per vehicle for the selected date.</CardDescription>
           </CardHeader>
           <CardContent>
             {/* Mock data for table until backend returns breakdown array */}
             <VehicleActivityTable vehicles={[]} /> 
           </CardContent>
        </Card>

        <div className="col-span-1 md:col-span-2 space-y-4">
          <ReportSubscriptionManager />
          
          <Card>
            <CardHeader>
              <CardTitle>Quick Insights</CardTitle>
            </CardHeader>
            <CardContent>
              <ul className="list-disc list-inside text-sm space-y-2 text-muted-foreground">
                <li>Most active hours: 9AM - 2PM</li>
                <li>Top vehicle by distance: Toyota HiAce (120km)</li>
                <li>Zero safety events recorded yesterday.</li>
              </ul>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
};

export default DailySummary;
