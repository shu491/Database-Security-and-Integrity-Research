# SQLDetc - SQL Injection Detection & Prevention Tool

A comprehensive SQL Injection Attack (SQLIA) detection and prevention system developed for Oracle Database Management Systems. This research project demonstrates various SQL injection techniques and implements an effective prevention tool with real-time monitoring capabilities.

## Project Overview
This project was developed as part of the **SEG2102 Database Management Systems** course at the School of Engineering and Technology. The research focuses on:

- Demonstrating 8 major types of SQL Injection Attacks
- Developing SQLDetc - a custom detection and prevention tool
- Evaluating prevention effectiveness in controlled environments
- Providing practical security solutions for Oracle DBMS

## Key Features

### Attack Detection
- **Comprehensive Pattern Matching**: 157 predefined SQL injection patterns
- **Real-time Monitoring**: Continuous database activity surveillance
- **Multiple Attack Type Detection**:
  - Tautologies
  - Union Queries
  - Piggy-backed Queries
  - Stored Procedures
  - Boolean-based & Time-based Inference
  - Alternate Encodings
  - Logical Incorrect Queries

### Prevention Mechanisms
- **Parameterized Query Enforcement**
- **Input Validation & Sanitization**
- **Real-time Query Interception**
- **Automated Audit Logging**
- **Risk Assessment & Recommendations**

### Performance Metrics
- **Detection Rate**: 95.8% overall
- **False Positives**: < 3%
- **Response Time**: ~4.5ms average
- **Test Scale**: 500 concurrent connections, 2,000 queries/minute

## System Architecture
The system employs a multi-layered defense strategy:

1. **Static Analysis Layer**: AST generation and query parsing
2. **Pattern Matching**: 157 SQL injection patterns with risk classification
3. **Profile Generation**: Behavioral analysis of normal database operations
4. **Real-time Enforcement**: Oracle database plugin for query interception

## Installation & Setup

### Prerequisites
- Oracle Database 19c or higher
- Python 3.8+
- Oracle Instant Client
- cx_Oracle library
