import tkinter as tk
from tkinter import ttk, scrolledtext, messagebox
import cx_Oracle
import logging
from datetime import datetime
from typing import Dict, List
from dataclasses import dataclass

logging.basicConfig(
    filename='sql_injection_monitor.log',
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)

@dataclass
class DetectionResult:
    query: str
    attack_types: List[str]
    timestamp: datetime
    risk_level: str
    recommendations: List[str]
    db_user: str = ''
    session_id: str = ''
    ip_address: str = ''

class DatabaseMonitor:
    def __init__(self, connection_config: Dict[str, str]):
        self.connection_config = connection_config
        
    def check_database_attacks(self) -> List[Dict]:
        """Query the database audit table for recent attacks"""
        try:
            with cx_Oracle.connect(**self.connection_config) as connection:
                cursor = connection.cursor()
                query = """
                SELECT audit_id, sql_text, user_name, ip_address, 
                       attack_type, risk_level, detection_time
                FROM sql_injection_audit
                WHERE detection_time > SYSDATE - 1/24
                ORDER BY detection_time DESC
                """
                cursor.execute(query)
                results = []
                for row in cursor:
                    results.append({
                        'audit_id': row[0],
                        'sql_text': row[1],
                        'user_name': row[2],
                        'ip_address': row[3],
                        'attack_type': row[4],
                        'risk_level': row[5],
                        'detection_time': row[6]
                    })
                return results
        except cx_Oracle.DatabaseError as e:
            logging.error(f"Database error: {str(e)}")
            return []

    def execute_analysis_query(self, query: str) -> DetectionResult:
        """Execute analysis using database detection package"""
        try:
            with cx_Oracle.connect(**self.connection_config) as connection:
                cursor = connection.cursor()
                # Call the database package to check for injection
                cursor.execute("""
                    DECLARE
                        l_attack_type VARCHAR2(100);
                    BEGIN
                        l_attack_type := sql_injection_detector.check_sql_injection(:query);
                        IF l_attack_type IS NOT NULL THEN
                            sql_injection_detector.log_suspicious_activity(
                                :query,
                                l_attack_type,
                                'HIGH'
                            );
                        END IF;
                        :result := l_attack_type;
                    END;
                """, query=query, result=cursor.var(str))
                
                attack_type = cursor.var.getvalue()
                
                if attack_type:
                    # Get session info
                    cursor.execute("""
                        SELECT SYS_CONTEXT('USERENV', 'SESSION_USER'),
                               SYS_CONTEXT('USERENV', 'SID'),
                               SYS_CONTEXT('USERENV', 'IP_ADDRESS')
                        FROM DUAL
                    """)
                    db_user, session_id, ip_address = cursor.fetchone()
                    
                    return DetectionResult(
                        query=query,
                        attack_types=[attack_type],
                        timestamp=datetime.now(),
                        risk_level='HIGH',
                        recommendations=self._get_recommendations(attack_type),
                        db_user=db_user,
                        session_id=session_id,
                        ip_address=ip_address
                    )
                return DetectionResult(
                    query=query,
                    attack_types=[],
                    timestamp=datetime.now(),
                    risk_level='LOW',
                    recommendations=[]
                )
        except cx_Oracle.DatabaseError as e:
            logging.error(f"Database error during analysis: {str(e)}")
            raise

    def _get_recommendations(self, attack_type: str) -> List[str]:
        """Get recommendations based on attack type"""
        recommendations = {
            'Tautology Attack': [
                'Use parameterized queries',
                'Implement input validation',
                'Add WAF rules'
            ],
            'Union-Based Attack': [
                'Use parameterized queries',
                'Implement column count validation',
                'Restrict UNION operations'
            ],
            'Piggy-Backed Query Attack': [
                'Use query parameters',
                'Restrict multiple queries',
                'Implement query length limits'
            ],
            'Time-Based Attack': [
                'Set query timeout limits',
                'Monitor for delayed responses',
                'Implement rate limiting'
            ],
            'Stored Procedure Attack': [
                'Restrict stored procedure access',
                'Implement procedure whitelisting',
                'Regular security audits'
            ]
        }
        return recommendations.get(attack_type, ['Review query for security issues'])

class SQLInjectionAnalyzer(tk.Tk):
    def __init__(self, db_config: Dict[str, str]):
        super().__init__()
        
        self.title("SQL Injection Detection & Prevention Tool")
        self.geometry("1200x800")
        
        self.db_monitor = DatabaseMonitor(db_config)
        self.setup_gui()
        self.start_monitoring()
        
    def setup_gui(self):
        # Create notebook for tabs
        self.notebook = ttk.Notebook(self)
        self.notebook.pack(fill='both', expand=True)
        
        # Analysis tab
        self.analysis_frame = ttk.Frame(self.notebook)
        self.notebook.add(self.analysis_frame, text="Query Analysis")
        self.setup_analysis_tab()
        
        # Monitoring tab
        self.monitoring_frame = ttk.Frame(self.notebook)
        self.notebook.add(self.monitoring_frame, text="Database Monitoring")
        self.setup_monitoring_tab()
        
    def setup_analysis_tab(self):
        # Query input area
        input_frame = ttk.LabelFrame(self.analysis_frame, text="SQL Query Analysis")
        input_frame.pack(fill='x', pady=5, padx=5)
        
        ttk.Label(input_frame, text="Enter SQL Query:").pack(anchor='w', padx=5)
        self.query_input = scrolledtext.ScrolledText(input_frame, height=5)
        self.query_input.pack(fill='x', padx=5, pady=5)
        
        # Control buttons
        button_frame = ttk.Frame(input_frame)
        button_frame.pack(fill='x', padx=5, pady=5)
        
        ttk.Button(
            button_frame,
            text="Analyze Query",
            command=self.analyze_query
        ).pack(side='left', padx=5)
        
        ttk.Button(
            button_frame,
            text="Clear Results",
            command=self.clear_results
        ).pack(side='left', padx=5)
        
        # Results area
        results_frame = ttk.LabelFrame(self.analysis_frame, text="Analysis Results")
        results_frame.pack(fill='both', expand=True, pady=5, padx=5)
        
        self.results_text = scrolledtext.ScrolledText(results_frame, height=20)
        self.results_text.pack(fill='both', expand=True, padx=5, pady=5)

    def setup_monitoring_tab(self):
        # Create monitoring display
        self.monitor_tree = ttk.Treeview(self.monitoring_frame, columns=(
            'time', 'user', 'ip', 'attack', 'risk', 'query'
        ))
        
        self.monitor_tree.heading('time', text='Time')
        self.monitor_tree.heading('user', text='User')
        self.monitor_tree.heading('ip', text='IP Address')
        self.monitor_tree.heading('attack', text='Attack Type')
        self.monitor_tree.heading('risk', text='Risk Level')
        self.monitor_tree.heading('query', text='Query')
        
        self.monitor_tree.column('time', width=150)
        self.monitor_tree.column('user', width=100)
        self.monitor_tree.column('ip', width=120)
        self.monitor_tree.column('attack', width=150)
        self.monitor_tree.column('risk', width=80)
        self.monitor_tree.column('query', width=300)
        
        self.monitor_tree.pack(fill='both', expand=True, padx=5, pady=5)
        
        # Control frame
        control_frame = ttk.Frame(self.monitoring_frame)
        control_frame.pack(fill='x', padx=5, pady=5)
        
        ttk.Button(
            control_frame,
            text="Refresh",
            command=self.refresh_monitoring
        ).pack(side='left', padx=5)
        
        self.auto_refresh_var = tk.BooleanVar(value=True)
        ttk.Checkbutton(
            control_frame,
            text="Auto Refresh",
            variable=self.auto_refresh_var
        ).pack(side='left', padx=5)

    def analyze_query(self):
        query = self.query_input.get('1.0', 'end-1c').strip()
        
        if not query:
            messagebox.showwarning("Input Required", "Please enter a SQL query to analyze.")
            return
            
        try:
            result = self.db_monitor.execute_analysis_query(query)
            self.display_results(result)
        except Exception as e:
            messagebox.showerror("Error", f"Analysis failed: {str(e)}")
        
    def display_results(self, result: DetectionResult):
        output = f"""
=== SQL Injection Analysis Results ===
Timestamp: {result.timestamp}

Query Analyzed:
{result.query}

Detection Results:
{'🚨 ATTACKS DETECTED:' if result.attack_types else '✅ No attacks detected'}
{self._format_attacks(result.attack_types) if result.attack_types else 'Query appears to be safe'}

Risk Level: {result.risk_level}

Database Context:
User: {result.db_user}
Session ID: {result.session_id}
IP Address: {result.ip_address}

Prevention Recommendations:
{self._format_recommendations(result.recommendations)}
===============================
"""
        self.results_text.delete('1.0', tk.END)
        self.results_text.insert('1.0', output)
        
        if result.attack_types:
            self.results_text.tag_add("warning", "3.0", "4.0")
            self.results_text.tag_config("warning", foreground="red")
            
    def _format_attacks(self, attacks: List[str]) -> str:
        return '\n'.join(f"- {attack}" for attack in attacks)
        
    def _format_recommendations(self, recommendations: List[str]) -> str:
        return '\n'.join(f"- {rec}" for rec in recommendations)
        
    def clear_results(self):
        self.query_input.delete('1.0', tk.END)
        self.results_text.delete('1.0', tk.END)

    def refresh_monitoring(self):
        """Refresh the monitoring display"""
        try:
            attacks = self.db_monitor.check_database_attacks()
            
            # Clear existing items
            for item in self.monitor_tree.get_children():
                self.monitor_tree.delete(item)
            
            # Add new items
            for attack in attacks:
                self.monitor_tree.insert('', 'end', values=(
                    attack['detection_time'],
                    attack['user_name'],
                    attack['ip_address'],
                    attack['attack_type'],
                    attack['risk_level'],
                    attack['sql_text'][:50] + '...' if len(attack['sql_text']) > 50 else attack['sql_text']
                ))
        except Exception as e:
            logging.error(f"Error refreshing monitoring display: {str(e)}")

    def start_monitoring(self):
        """Start the monitoring refresh cycle"""
        def refresh_cycle():
            if self.auto_refresh_var.get():
                self.refresh_monitoring()
            self.after(30000, refresh_cycle)  # Refresh every 30 seconds
        
        refresh_cycle()

def main():
    # Configure your database connection
    db_config = {
        'user': 'your_username',
        'password': 'your_password',
        'dsn': 'your_database'
    }
    
    app = SQLInjectionAnalyzer(db_config)
    app.mainloop()

if __name__ == "__main__":
    main()